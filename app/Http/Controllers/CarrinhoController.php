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
                $carrinho[$id]['estoque'] = $produto->quantidade;

                if ($produto->quantidade > 0 && $carrinho[$id]['quantidade'] > $produto->quantidade) {
                    $carrinho[$id]['quantidade'] = $produto->quantidade;
                    session()->flash('error', "A quantidade de {$produto->nome} foi ajustada para o estoque disponível ({$produto->quantidade}).");
                }
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
        $noCarrinho = $carrinho[$id]['quantidade'] ?? 0;

        if ($produto->quantidade <= 0) {
            return redirect()->back()->with('error', 'Produto esgotado.');
        }

        if ($noCarrinho >= $produto->quantidade) {
            return redirect()->back()->with('error', "Só temos {$produto->quantidade} unidade(s) de {$produto->nome} em estoque.");
        }

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
                $estoque = Produto::whereKey($id)->value('quantidade') ?? 0;

                if ($carrinho[$id]['quantidade'] >= $estoque) {
                    $mensagem = "Só temos {$estoque} unidade(s) desse produto em estoque.";

                    if ($request->expectsJson() || $request->isJson()) {
                        return response()->json(['success' => false, 'message' => $mensagem], 422);
                    }

                    return redirect()->back()->with('error', $mensagem);
                }

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

        if ($request->expectsJson() || $request->isJson()) {
            $total = collect($carrinho)->sum(fn ($item) => $item['preço'] * $item['quantidade']);
            $item  = $carrinho[$id] ?? null;

            return response()->json([
                'success'    => true,
                'removido'   => $item === null,
                'quantidade' => $item['quantidade'] ?? 0,
                'subtotal'   => $item ? 'R$ ' . number_format($item['preço'] * $item['quantidade'], 2, ',', '.') : null,
                'total'      => 'R$ ' . number_format($total, 2, ',', '.'),
                'totalValor' => $total,
            ]);
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