<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Pedido;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
 
class CheckoutController extends Controller
{
    private function getSettingsPath() { return storage_path('app/settings.json'); }
 
    private function getSettings()
    {
        if (!file_exists($this->getSettingsPath())) {
            return [
                'logo' => null,
                'banners' => [],
                'pagamento_pix' => true,
                'pagamento_credito' => true,
                'pagamento_debito' => true
            ];
        }
        $settings = json_decode(file_get_contents($this->getSettingsPath()), true);
        if (!isset($settings['banners'])) {
            $settings['banners'] = [];
        }
        return $settings;
    }
 
    private function abacate()
    {
        return Http::withToken(config('services.abacatepay.key'))
            ->acceptJson()
            ->timeout(30)
            ->baseUrl('https://api.abacatepay.com/v2');
    }
 
    private function totalCentavos(array $carrinho): int
    {
        $total = 0;
        foreach ($carrinho as $item) {
            $total += (int) round(((float) $item['preço']) * 100) * (int) $item['quantidade'];
        }
        return $total;
    }
 
    public function iniciar(Request $request, $id)
    {
        $produto = Produto::findOrFail($id);
        $carrinho = session()->get('carrinho', []);
 
        if(isset($carrinho[$id])) {
            $carrinho[$id]['quantidade']++;
        } else {
            $carrinho[$id] = [
                "nome" => $produto->nome,
                "quantidade" => 1,
                "preço" => $produto->preço,
                "imagem" => $produto->imagem
            ];
        }
 
        session()->put('carrinho', $carrinho);
        return redirect()->route('carrinho.index');
    }
 
    public function index()
    {
        if (!session()->has('carrinho') || count(session('carrinho')) == 0) {
            return redirect()->route('carrinho.index')->with('error', 'Seu carrinho está vazio.');
        }
        return redirect()->route('carrinho.index');
    }
 
    public function configuracoesForm()
    {
        $config = $this->getSettings();
        return view('produtos.configuracoes', ['configuracoes' => (object) $config]);
    }
 
    public function salvarConfiguracoes(Request $request)
    {
        $config = $this->getSettings();
 
        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048']);
            if (!empty($config['logo']) && Storage::disk('public')->exists($config['logo'])) {
                Storage::disk('public')->delete($config['logo']);
            }
            $config['logo'] = $request->file('logo')->store('config', 'public');
        }
 
        $banners = $config['banners'] ?? [];
        if ($request->hasFile('banners')) {
            $request->validate(['banners.*' => 'image|mimes:jpeg,png,jpg,gif|max:4096']);
            foreach ($request->file('banners') as $bannerFile) {
                $path = $bannerFile->store('banners', 'public');
                $banners[] = $path;
            }
        }
        $config['banners'] = $banners;
 
        $config['pagamento_pix'] = $request->has('pagamento_pix');
        $config['pagamento_credito'] = $request->has('pagamento_credito');
        $config['pagamento_debito'] = $request->has('pagamento_debito');
 
        file_put_contents($this->getSettingsPath(), json_encode($config, JSON_PRETTY_PRINT));
        return redirect()->back()->with('success', 'Configurações salvas com sucesso!');
    }
 
    public function removerBanner($index)
    {
        $config = $this->getSettings();
        if (isset($config['banners'][$index])) {
            if (Storage::disk('public')->exists($config['banners'][$index])) {
                Storage::disk('public')->delete($config['banners'][$index]);
            }
            unset($config['banners'][$index]);
            $config['banners'] = array_values($config['banners']);
            file_put_contents($this->getSettingsPath(), json_encode($config, JSON_PRETTY_PRINT));
        }
        return redirect()->back()->with('success', 'Banner removido com sucesso!');
    }
 
    public function enderecoForm()
    {
        return redirect()->route('carrinho.index');
    }
 
    public function salvarEndereco(Request $request)
    {
        $dadosEndereco = $request->validate([
            'cep' => 'required|string|max:9',
            'logradouro' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
        ]);
 
        session(['checkout_endereco' => $dadosEndereco]);
 
        return redirect()->route('checkout.pagamento');
    }
 
    public function pagamentoForm()
    {
        if (!session()->has('checkout_endereco')) {
            return redirect()->route('carrinho.index');
        }
 
        $endereco = session('checkout_endereco');
        $config = $this->getSettings();
        $carrinho = session('carrinho', []);
 
        if (empty($carrinho)) {
            return redirect()->route('carrinho.index');
        }
 
        $totalCentavos = $this->totalCentavos($carrinho);
 
        return view('produtos.pagamento', compact('endereco', 'config', 'carrinho', 'totalCentavos'));
    }
 
    public function finalizarCompra(Request $request)
    {
        $request->validate([
            'metodo_pagamento' => 'required|in:pix,credito,boleto,debito',
            'nome'             => 'required|string|max:150',
            'cpf'              => 'required|string|min:11|max:18',
            'celular'          => 'required|string|min:8|max:20',
 
            'cartao_numero'    => 'required_if:metodo_pagamento,credito|nullable|string',
            'cartao_nome'      => 'required_if:metodo_pagamento,credito|nullable|string|max:100',
            'cartao_validade'  => 'required_if:metodo_pagamento,credito|nullable|string',
            'cartao_cvv'       => 'required_if:metodo_pagamento,credito|nullable|string',
            'cartao_parcelas'  => 'nullable|integer|min:1|max:12',
        ], [
            'cartao_numero.required_if'   => 'Informe o número do cartão.',
            'cartao_nome.required_if'     => 'Informe o nome impresso no cartão.',
            'cartao_validade.required_if' => 'Informe a validade do cartão.',
            'cartao_cvv.required_if'      => 'Informe o CVV do cartão.',
        ]);
 
        $metodo = $request->input('metodo_pagamento') === 'debito' ? 'boleto' : $request->input('metodo_pagamento');
 
        $carrinho = session()->get('carrinho', []);
        if (empty($carrinho)) {
            return response()->json(['success' => false, 'message' => 'Carrinho vazio.'], 400);
        }
 
        $amount = $this->totalCentavos($carrinho);
        if ($amount < 100) {
            return response()->json(['success' => false, 'message' => 'O valor mínimo para pagamento é R$ 1,00.'], 400);
        }
 
        $user = Auth::user();
 
        $customer = [
            'name'      => $request->input('nome'),
            'email'     => $user->email,
            'taxId'     => preg_replace('/\D/', '', $request->input('cpf')),
            'cellphone' => $request->input('celular'),
        ];
 
        $pedidoId  = 'GN-' . $user->id . '-' . now()->format('YmdHis');
        $descricao = Str::limit(
            'Pedido ' . $pedidoId . ' - ' . collect($carrinho)->map(fn ($i) => $i['quantidade'] . 'x ' . $i['nome'])->implode(', '),
            490
        );
 
        switch ($metodo) {
            case 'pix':
                $payload = [
                    'method' => 'PIX',
                    'data' => [
                        'amount'      => $amount,
                        'description' => $descricao,
                        'expiresIn'   => 3600,
                        'customer'    => $customer,
                        'externalId'  => $pedidoId,
                        'metadata'    => ['pedidoId' => $pedidoId],
                    ],
                ];
                break;
 
            case 'credito':
                return $this->simularCartao($request, $carrinho, $amount, $customer, $pedidoId);
 
            default:
                $payload = [
                    'method' => 'BOLETO',
                    'data' => [
                        'amount'      => $amount,
                        'dueDate'     => now()->addDays(3)->toDateString(),
                        'description' => $descricao,
                        'customer'    => $customer,
                        'externalId'  => $pedidoId,
                        'metadata'    => ['pedidoId' => $pedidoId],
                    ],
                ];
        }
 
        $response = $this->abacate()->post('/transparents/create', $payload);
        $json = $response->json() ?? [];
 
        if (!$response->successful() || empty($json['data'])) {
            Log::error('AbacatePay transparents/create falhou', [
                'metodo' => $metodo,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
 
            return response()->json([
                'success' => false,
                'message' => match ($json['error'] ?? null) {
                    'Invalid taxId' => 'CPF inválido. Verifique o número informado.',
                    default         => $json['error'] ?? 'Erro ao criar a cobrança no AbacatePay.',
                },
            ], 400);
        }
 
        return $this->concluirPedido($json['data'], $metodo, $carrinho, $amount, $customer, $pedidoId);
    }
 
    private function concluirPedido(array $cobranca, string $metodo, array $carrinho, int $amount, array $customer, string $pedidoId)
    {
        $endereco = session('checkout_endereco');

        $pedido = Pedido::create([
            'codigo'          => $pedidoId,
            'user_id'         => Auth::id(),
            'metodo'          => $metodo,
            'status'          => $cobranca['status'] ?? 'PENDING',
            'total'           => $amount,
            'parcelas'        => $cobranca['installmentCount'] ?? null,
            'cartao_bandeira' => $cobranca['brand'] ?? null,
            'cartao_final'    => $cobranca['lastFour'] ?? null,
            'gateway_id'      => $cobranca['id'] ?? null,
            'cliente'         => $customer,
            'endereco'        => $endereco,
        ]);

        foreach ($carrinho as $produtoId => $item) {
            $pedido->itens()->create([
                'produto_id' => Produto::whereKey($produtoId)->exists() ? $produtoId : null,
                'nome'       => $item['nome'],
                'preco'      => $item['preço'],
                'quantidade' => $item['quantidade'],
                'imagem'     => $item['imagem'] ?? null,
            ]);
        }

        session()->put('ultima_cobranca', $cobranca);
        session()->put('metodo_escolhido', $metodo);
        session()->put('pedido', [
            'id'       => $pedidoId,
            'itens'    => $carrinho,
            'endereco' => $endereco,
            'cliente'  => $customer,
            'total'    => $amount,
        ]);
        session()->forget(['checkout_endereco', 'carrinho']);
 
        return response()->json([
            'success'      => true,
            'redirect_url' => route('checkout.sucesso'),
        ]);
    }
 
    private function simularCartao(Request $request, array $carrinho, int $amount, array $customer, string $pedidoId)
    {
        $numero   = preg_replace('/\D/', '', $request->input('cartao_numero'));
        $cvv      = preg_replace('/\D/', '', $request->input('cartao_cvv'));
        $parcelas = (int) ($request->input('cartao_parcelas') ?: 1);
        $bandeira = $this->bandeiraCartao($numero);
 
        $recusar = fn (string $msg) => response()->json(['success' => false, 'message' => $msg], 400);
 
        if (strlen($numero) < 13 || strlen($numero) > 19 || !$this->luhnValido($numero)) {
            return $recusar('Número do cartão inválido.');
        }
 
        if (!preg_match('/^(0[1-9]|1[0-2])\/?(\d{2})$/', trim($request->input('cartao_validade')), $m)) {
            return $recusar('Validade inválida. Use o formato MM/AA.');
        }
        $fimDaValidade = \Carbon\Carbon::createFromDate(2000 + (int) $m[2], (int) $m[1], 1)->endOfMonth();
        if ($fimDaValidade->isPast()) {
            return $recusar('Cartão vencido.');
        }
 
        if (strlen($cvv) !== ($bandeira === 'AMEX' ? 4 : 3)) {
            return $recusar('CVV inválido.');
        }
 
        if ($parcelas > 1 && intdiv($amount, $parcelas) < 1000) {
            return $recusar('Cada parcela precisa ser de pelo menos R$ 10,00.');
        }
 
        $cartoesTeste = [
            '4000000000000002' => 'Pagamento recusado pelo banco emissor.',
            '4000000000000069' => 'Cartão expirado.',
            '4000000000000127' => 'CVV incorreto.',
        ];
        if (isset($cartoesTeste[$numero])) {
            return $recusar($cartoesTeste[$numero]);
        }
 
        $cobranca = [
            'id'                => 'card_sim_' . Str::random(20),
            'amount'            => $amount,
            'status'            => 'APPROVED',
            'devMode'           => true,
            'brand'             => $bandeira,
            'lastFour'          => substr($numero, -4),
            'installmentCount'  => $parcelas,
            'installmentAmount' => intdiv($amount, $parcelas),
            'createdAt'         => now()->toIso8601String(),
            'simulado'          => true,
        ];
 
        return $this->concluirPedido($cobranca, 'credito', $carrinho, $amount, $customer, $pedidoId);
    }
 
    private function luhnValido(string $numero): bool
    {
        $soma = 0;
        $dobrar = false;
        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $d = (int) $numero[$i];
            if ($dobrar) {
                $d *= 2;
                if ($d > 9) $d -= 9;
            }
            $soma += $d;
            $dobrar = !$dobrar;
        }
        return $soma % 10 === 0;
    }
 
    private function bandeiraCartao(string $numero): string
    {
        return match (true) {
            (bool) preg_match('/^(4011|4312|4389|4514|4576|5041|5066|5067|509|6277|6362|6363|650|6516|6550)/', $numero) => 'ELO',
            (bool) preg_match('/^(606282|3841)/', $numero)      => 'HIPERCARD',
            (bool) preg_match('/^3[47]/', $numero)              => 'AMEX',
            (bool) preg_match('/^4/', $numero)                  => 'VISA',
            (bool) preg_match('/^(5[1-5]|2[2-7])/', $numero)    => 'MASTERCARD',
            default                                             => 'CARTÃO',
        };
    }
 
    public function sucesso()
    {
        $cobranca = session()->get('ultima_cobranca');
        $metodo   = session()->get('metodo_escolhido');
        $pedido   = session()->get('pedido', []);
 
        if (!$cobranca) {
            return redirect()->route('produtos.index');
        }
 
        return view('produtos.sucesso', compact('cobranca', 'metodo', 'pedido'));
    }
 
    private function atualizarStatusPedido(array $cobranca): void
    {
        if (!empty($cobranca['id']) && !empty($cobranca['status'])) {
            Pedido::where('gateway_id', $cobranca['id'])->update(['status' => $cobranca['status']]);
        }
    }

    public function statusPagamento()
    {
        $cobranca = session('ultima_cobranca');
        if (!$cobranca) {
            return response()->json(['status' => null], 404);
        }
 
        $endpoint = str_starts_with($cobranca['id'], 'pix_') ? '/transparents/check' : '/transparents/get';
        $response = $this->abacate()->get($endpoint, ['id' => $cobranca['id']]);
 
        if ($response->successful() && isset($response['data']['status'])) {
            $cobranca['status'] = $response['data']['status'];
            session()->put('ultima_cobranca', $cobranca);
            $this->atualizarStatusPedido($cobranca);
        }
 
        return response()->json(['status' => $cobranca['status'] ?? null]);
    }
 
    public function simularPagamento()
    {
        $cobranca = session('ultima_cobranca');
        $metodo   = session('metodo_escolhido');

        if (!$cobranca) {
            return response()->json(['success' => false, 'message' => 'Nenhuma cobrança encontrada.'], 404);
        }

        if ($metodo === 'boleto') {
            $cobranca['status'] = 'PAID';
            session()->put('ultima_cobranca', $cobranca);
            $this->atualizarStatusPedido($cobranca);

            return response()->json(['success' => true, 'status' => 'PAID']);
        }
 
        $response = $this->abacate()
            ->withBody('{}', 'application/json')
            ->post('/transparents/simulate-payment?id=' . urlencode($cobranca['id']));
 
        if (!$response->successful()) {
            Log::error('AbacatePay simulate-payment falhou: ' . $response->body());
            return response()->json([
                'success' => false,
                'message' => $response->json('error') ?? 'Não foi possível simular o pagamento.',
            ], 400);
        }
 
        $cobranca['status'] = $response->json('data.status') ?? 'PAID';
        session()->put('ultima_cobranca', $cobranca);
        $this->atualizarStatusPedido($cobranca);
 
        return response()->json(['success' => true, 'status' => $cobranca['status']]);
    }
 
    public function boletoPdf()
    {
        $cobranca = session('ultima_cobranca');
        $metodo   = session('metodo_escolhido');
        $pedido   = session('pedido', []);
 
        if (!$cobranca || $metodo !== 'boleto') {
            abort(404);
        }
 
        $barcodeImg = null;
        $digitos = preg_replace('/\D/', '', $cobranca['barCode'] ?? '');
 
        if (strlen($digitos) === 47 && class_exists(\Picqer\Barcode\BarcodeGeneratorPNG::class)) {
            $codigoBarras = substr($digitos, 0, 4)
                          . substr($digitos, 32, 1)
                          . substr($digitos, 33, 14)
                          . substr($digitos, 4, 5)
                          . substr($digitos, 10, 10)
                          . substr($digitos, 21, 10);
 
            $generator  = new \Picqer\Barcode\BarcodeGeneratorPNG();
            $barcodeImg = 'data:image/png;base64,' . base64_encode(
                $generator->getBarcode($codigoBarras, $generator::TYPE_INTERLEAVED_2_5, 2, 60)
            );
        }
 
        $pdf = Pdf::loadView('produtos.boleto-pdf', [
            'cobranca'   => $cobranca,
            'pedido'     => $pedido,
            'barcodeImg' => $barcodeImg,
        ])->setPaper('a4');
 
        return $pdf->download('boleto-' . ($pedido['id'] ?? $cobranca['id']) . '.pdf');
    }
}