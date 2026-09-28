<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;

class CarrinhoController extends Controller
{
    public function index()
    {
        $carrinho = session()->get('carrinho', []);

        foreach ($carrinho as $id => $item) {
            $produto = Produto::find($id);
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
        return redirect()->back()->with('success', 'Produto adicionado ao carrinho!');
    }

    public function atualizar(Request $request, $id)
    {
        $carrinho = session()->get('carrinho', []);

        if (isset($carrinho[$id])) {
            // Pega a ação vinda do JSON, de parâmetros comuns ou de query string (?acao=menos)
            $acao = $request->input('acao') ?? $request->get('acao');

            if ($acao === 'mais') {
                $carrinho[$id]['quantidade']++;
            } elseif ($acao === 'menos') {
                $carrinho[$id]['quantidade']--;

                // Se a quantidade chegar a 0 ou menos, remove o item
                if ($carrinho[$id]['quantidade'] <= 0) {
                    unset($carrinho[$id]);
                }
            }

            session()->put('carrinho', $carrinho);
        }

        // Se a requisição veio via JavaScript/Fetch, retorna um JSON limpo
        if ($request->expectsJson() || $request->isJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Carrinho atualizado com sucesso!');
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