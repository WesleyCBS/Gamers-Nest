<?php

namespace App\Http\Controllers;

use App\Models\Roupa;
use Illuminate\Http\Request;

class CarrinhoController extends Controller
{
    // Exibe a página com os itens que estão no carrinho
    public function index()
    {
        $carrinho = session()->get('carrinho', []);
        return view('carrinho.index', compact('carrinho'));
    }

    // Adiciona um produto ao carrinho
    public function adicionar($id)
    {
        $produto = Roupa::findOrFail($id);
        $carrinho = session()->get('carrinho', []);

        // Se o produto já estiver no carrinho, apenas aumenta a quantidade
        if(isset($carrinho[$id])) {
            $carrinho[$id]['quantidade']++;
        } else {
            // Se não estiver, adiciona os dados básicos dele
            $carrinho[$id] = [
                "nome" => $produto->nome,
                "quantidade" => 1,
                "preço" => $produto->preço,
                "imagem" => $produto->imagem
            ];
        }

        session()->put('carrinho', $carrinho);
        return redirect()->back()->with('success', 'Produto adicionado ao carrinho!');
    }

    // Remove um produto específico do carrinho
    public function remover($id)
    {
        $carrinho = session()->get('carrinho', []);

        if(isset($carrinho[$id])) {
            unset($carrinho[$id]);
            session()->put('carrinho', $carrinho);
        }

        return redirect()->back()->with('success', 'Produto removido do carrinho!');
    }
}
