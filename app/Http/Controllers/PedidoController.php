<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PedidoController extends Controller
{
    public function pagar(Request $request, $id)
    {
        $pedido = $request->user()->pedidos()->with('itens')->findOrFail($id);

        if (!$pedido->pode_pagar) {
            return redirect()->route('pedidos.meus')->with('error', 'Este pedido não está aguardando pagamento.');
        }

        $cobranca = $pedido->cobranca;
        $expirou  = false;

        if (!empty($cobranca['expiresAt'])) {
            $validade = Carbon::parse($cobranca['expiresAt']);
            $expirou  = ($pedido->metodo === 'boleto' ? $validade->endOfDay() : $validade)->isPast();
        }

        if (empty($cobranca) || empty($cobranca['id']) || $expirou) {
            $cobranca = $this->novaCobranca($pedido);

            if (!$cobranca) {
                return redirect()->route('pedidos.meus')->with('error', 'Não foi possível gerar o pagamento. Tente novamente.');
            }

            $pedido->update([
                'cobranca'   => $cobranca,
                'gateway_id' => $cobranca['id'],
                'status'     => $cobranca['status'] ?? 'PENDING',
            ]);
        }

        session()->put('ultima_cobranca', $cobranca);
        session()->put('metodo_escolhido', $pedido->metodo);
        session()->put('pedido', [
            'id'       => $pedido->codigo,
            'itens'    => $pedido->itens->map(fn ($i) => [
                'nome'       => $i->nome,
                'preço'      => $i->preco,
                'quantidade' => $i->quantidade,
                'imagem'     => $i->imagem,
            ])->all(),
            'endereco' => $pedido->endereco,
            'cliente'  => $pedido->cliente,
            'total'    => $pedido->total,
        ]);

        return redirect()->route('checkout.sucesso');
    }

    public function cancelar(Request $request, $id)
    {
        $pedido = $request->user()->pedidos()->with('itens')->findOrFail($id);

        if (!$pedido->pode_cancelar) {
            return redirect()->back()->with('error', 'Só é possível cancelar pedidos que ainda estão aguardando pagamento.');
        }

        DB::transaction(function () use ($pedido, $request) {
            $pedido->update([
                'status'             => 'CANCELLED',
                'cancelado_por'      => 'cliente',
                'cancelado_por_nome' => $request->user()->name,
                'cancelado_em'       => now(),
            ]);
            $pedido->devolverEstoque();
        });

        $cobranca = session('ultima_cobranca');
        if ($cobranca && ($cobranca['id'] ?? null) === $pedido->gateway_id) {
            $cobranca['status'] = 'CANCELLED';
            session()->put('ultima_cobranca', $cobranca);
        }

        return redirect()->back()->with('success', "Pedido {$pedido->codigo} cancelado.");
    }

    public function solicitarCancelamento(Request $request, $id)
    {
        $request->validate(['motivo' => 'nullable|string|max:500']);

        $pedido = $request->user()->pedidos()->findOrFail($id);

        if (!$pedido->pode_solicitar_cancelamento) {
            return redirect()->back()->with('error', 'Não é possível solicitar o cancelamento deste pedido.');
        }

        $pedido->update([
            'cancelamento'               => 'solicitado',
            'cancelamento_motivo'        => $request->input('motivo') ?: null,
            'cancelamento_solicitado_em' => now(),
        ]);

        return redirect()->back()->with('success', "Cancelamento do pedido {$pedido->codigo} solicitado. Aguarde a análise da loja.");
    }

    public function responderCancelamento(Request $request, $id)
    {
        $request->validate(['acao' => 'required|in:aprovar,recusar']);

        $pedido = Pedido::with('itens')->findOrFail($id);

        if ($pedido->cancelamento !== 'solicitado') {
            return redirect()->back()->with('error', 'Este pedido não tem cancelamento aguardando análise.');
        }

        if ($request->input('acao') === 'aprovar') {
            DB::transaction(function () use ($pedido) {
                $pedido->update([
                    'status'             => 'REFUNDED',
                    'cancelamento'       => 'aprovado',
                    'cancelado_por'      => 'cliente',
                    'cancelado_por_nome' => $pedido->cliente['name'] ?? $pedido->user?->name,
                    'cancelado_em'       => now(),
                ]);
                $pedido->devolverEstoque();
            });

            $mensagem = "Cancelamento do pedido {$pedido->codigo} aprovado. Valor reembolsado ao cliente.";
        } else {
            $pedido->update(['cancelamento' => 'recusado']);
            $mensagem = "Cancelamento do pedido {$pedido->codigo} recusado.";
        }

        return redirect()->back()->with('success', $mensagem);
    }

    public function etiqueta($id)
    {
        $pedido = Pedido::with(['itens', 'user'])->findOrFail($id);

        if (empty($pedido->endereco)) {
            return redirect()->back()->with('error', 'Este pedido não tem endereço de entrega.');
        }

        $gerador = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $cep     = preg_replace('/\D/', '', $pedido->endereco['cep'] ?? '');

        $barrasCep    = strlen($cep) === 8 ? $gerador->getBarcode($cep, $gerador::TYPE_CODE_128, 2, 40) : null;
        $barrasPedido = $gerador->getBarcode($pedido->codigo, $gerador::TYPE_CODE_128, 1, 40);

        return view('admin.etiqueta', compact('pedido', 'cep', 'barrasCep', 'barrasPedido'));
    }

    private function novaCobranca(Pedido $pedido): ?array
    {
        $cliente = $pedido->cliente ?? [];

        if (empty($cliente['taxId']) || empty($cliente['email'])) {
            return null;
        }

        $descricao = Str::limit(
            'Pedido ' . $pedido->codigo . ' - ' . $pedido->itens->map(fn ($i) => $i->quantidade . 'x ' . $i->nome)->implode(', '),
            490
        );

        $dados = [
            'amount'      => $pedido->total,
            'description' => $descricao,
            'customer'    => $cliente,
            'externalId'  => $pedido->codigo . '-' . now()->format('His'),
            'metadata'    => ['pedidoId' => $pedido->codigo],
        ];

        if ($pedido->metodo === 'pix') {
            $payload = ['method' => 'PIX', 'data' => $dados + ['expiresIn' => 3600]];
        } else {
            $payload = ['method' => 'BOLETO', 'data' => $dados + ['dueDate' => now()->addDays(3)->toDateString()]];
        }

        $response = Http::withToken(config('services.abacatepay.key'))
            ->acceptJson()
            ->timeout(30)
            ->post('https://api.abacatepay.com/v2/transparents/create', $payload);

        if (!$response->successful() || empty($response->json('data'))) {
            Log::error('AbacatePay: falha ao gerar nova cobrança', [
                'pedido' => $pedido->codigo,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        }

        return $response->json('data');
    }

    public function meus(Request $request)
    {
        $user  = $request->user();
        $query = $user->pedidos()->with('itens')->latest();

        if ($busca = trim((string) $request->input('busca'))) {
            $query->where(function ($q) use ($busca) {
                $q->where('codigo', 'like', "%{$busca}%")
                  ->orWhereHas('itens', fn ($i) => $i->where('nome', 'like', "%{$busca}%"));
            });
        }

        if ($request->input('status') === 'pago') {
            $query->whereIn('status', Pedido::STATUS_PAGOS);
        } elseif ($request->input('status') === 'pendente') {
            $query->where('status', 'PENDING');
        }

        if (in_array($request->input('metodo'), ['pix', 'credito', 'boleto'])) {
            $query->where('metodo', $request->input('metodo'));
        }

        $resumo = [
            'pedidos'   => $user->pedidos()->count(),
            'pagos'     => $user->pedidos()->whereIn('status', Pedido::STATUS_PAGOS)->count(),
            'pendentes' => $user->pedidos()->where('status', 'PENDING')->count(),
            'gasto'     => $user->pedidos()->whereIn('status', Pedido::STATUS_PAGOS)->sum('total') / 100,
        ];

        $pedidos = $query->paginate(10)->withQueryString();

        return view('pedidos.minhas-compras', compact('pedidos', 'resumo'));
    }

    public function admin(Request $request)
    {
        $query = Pedido::with(['itens', 'user'])->latest();

        if ($busca = trim((string) $request->input('busca'))) {
            $query->where(function ($q) use ($busca) {
                $q->where('codigo', 'like', "%{$busca}%")
                  ->orWhereHas('user', function ($u) use ($busca) {
                      $u->where('name', 'like', "%{$busca}%")
                        ->orWhere('email', 'like', "%{$busca}%");
                  });
            });
        }

        $abas = ['confirmar', 'concluidas', 'cancelamentos', 'cancelados', 'pagamento', 'todas'];
        $aba  = in_array($request->input('aba'), $abas) ? $request->input('aba') : 'confirmar';

        match ($aba) {
            'confirmar'     => $query->aConfirmar(),
            'concluidas'    => $query->concluidas(),
            'cancelamentos' => $query->cancelamentoSolicitado(),
            'cancelados'    => $query->cancelados(),
            'pagamento'     => $query->where('status', 'PENDING'),
            default         => null,
        };

        if (in_array($request->input('metodo'), ['pix', 'credito', 'boleto'])) {
            $query->where('metodo', $request->input('metodo'));
        }

        $contagens = [
            'confirmar'     => Pedido::aConfirmar()->count(),
            'concluidas'    => Pedido::concluidas()->count(),
            'cancelamentos' => Pedido::cancelamentoSolicitado()->count(),
            'cancelados'    => Pedido::cancelados()->count(),
            'pagamento'     => Pedido::where('status', 'PENDING')->count(),
            'todas'         => Pedido::count(),
        ];

        $faturamento = Pedido::whereIn('status', Pedido::STATUS_PAGOS)->sum('total') / 100;

        $pedidos = $query->paginate(10)->withQueryString();

        return view('admin.pedidos', compact('pedidos', 'aba', 'contagens', 'faturamento'));
    }

    public function cancelarAdmin(Request $request, $id)
    {
        $request->validate([
            'motivo' => 'required|string|min:3|max:500',
        ], [
            'motivo.required' => 'Informe o motivo do cancelamento.',
            'motivo.min'      => 'Escreva um motivo um pouco maior.',
        ]);

        $pedido = Pedido::with('itens')->findOrFail($id);

        if (!$pedido->pode_cancelar_admin) {
            return redirect()->back()->with('error', 'Este pedido não pode ser cancelado agora.');
        }

        DB::transaction(function () use ($pedido, $request) {
            $pedido->update([
                'status'              => 'REFUNDED',
                'cancelamento'        => 'admin',
                'cancelamento_motivo' => $request->input('motivo'),
                'cancelado_por'       => 'admin',
                'cancelado_por_nome'  => $request->user()->name,
                'cancelado_em'        => now(),
            ]);
            $pedido->devolverEstoque();
        });

        return redirect()->back()->with('success', "Venda {$pedido->codigo} cancelada e reembolsada. O pedido foi movido para Cancelados.");
    }

    public function confirmar($id)
    {
        $pedido = Pedido::findOrFail($id);

        if (!$pedido->pode_confirmar) {
            return redirect()->back()->with('error', 'Este pedido não pode ser confirmado agora.');
        }

        $pedido->update(['confirmado_em' => now()]);

        return redirect()->back()->with('success', "Venda {$pedido->codigo} confirmada e movida para Concluídas.");
    }
}
