<?php

namespace App\Http\Controllers;

use App\Models\Roupa;
use Illuminate\Http\Request;

class CarrinhoController extends Controller
{
    public function index()
    {
        $carrinho = session()->get('carrinho', []);

        foreach ($carrinho as $id => $item) {
            $produto = Roupa::find($id);
            if ($produto) {
                $carrinho[$id]['nome'] = $produto->nome;
                $carrinho[$id]['preço'] = $produto->preço;
                $carrinho[$id]['imagem'] = $produto->imagem;
            } else {
                unset($carrinho[$id]);
            }
        }

        session()->put('carrinho', $carrinho);
        return view('carrinho.index', compact('carrinho'));
    }

    public function adicionar($id)
    {
        $produto = Roupa::findOrFail($id);
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
        return redirect()->back()->with('success', 'Produto adicionado ao carrinho!');
    }

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