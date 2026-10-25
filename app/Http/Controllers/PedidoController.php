<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
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

        if ($request->input('status') === 'pago') {
            $query->whereIn('status', Pedido::STATUS_PAGOS);
        } elseif ($request->input('status') === 'pendente') {
            $query->where('status', 'PENDING');
        }

        if (in_array($request->input('metodo'), ['pix', 'credito', 'boleto'])) {
            $query->where('metodo', $request->input('metodo'));
        }

        $resumo = [
            'pedidos'     => Pedido::count(),
            'pagos'       => Pedido::whereIn('status', Pedido::STATUS_PAGOS)->count(),
            'pendentes'   => Pedido::where('status', 'PENDING')->count(),
            'faturamento' => Pedido::whereIn('status', Pedido::STATUS_PAGOS)->sum('total') / 100,
        ];

        $pedidos = $query->paginate(15)->withQueryString();

        return view('admin.pedidos', compact('pedidos', 'resumo'));
    }
}
