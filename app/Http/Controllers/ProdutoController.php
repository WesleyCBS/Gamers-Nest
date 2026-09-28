<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $query = Produto::with('categoria');

        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nome', 'LIKE', "%{$search}%")
                  ->orWhere('descrição', 'LIKE', "%{$search}%");
            });
        }

        $produtos = $query->paginate(10)->withQueryString();
        $categorias = Categoria::all(); 
        return view('produtos.index', compact('produtos', 'categorias'));
    }

    public function porCategoria(Request $request, $slug)
    {
        $categoriaAtiva = Categoria::where('slug', $slug)->firstOrFail();
        
        $query = Produto::where('categoria_id', $categoriaAtiva->id);

        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nome', 'LIKE', "%{$search}%")
                  ->orWhere('descrição', 'LIKE', "%{$search}%");
            });
        }
        
        $produtos = $query->paginate(10)->withQueryString();
        $categorias = Categoria::all(); 

        return view('produtos.index', compact('produtos', 'categorias', 'categoriaAtiva'));
    }

    public function show($id) 
    {
        $produto = Produto::with('categoria')->findOrFail($id);
        return view('produtos.show', compact('produto'));
    }

    public function create()
    {
        $categorias = Categoria::all(); 
        return view('produtos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'descrição' => 'nullable|string',
            'preço' => 'required|numeric',
            'quantidade' => 'required|integer',
            'categoria_id' => 'required|exists:categorias,id',
            'imagem' => 'required|image|max:2048',
        ]);

        if ($request->hasFile('imagem')) {
            $path = $request->file('imagem')->store('products', 'public');
            $data['imagem'] = $path; 
        }

        Produto::create($data);
        return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    public function edit($id) 
    {
        $produto = Produto::findOrFail($id);
        $categorias = Categoria::all();
        return view('produtos.edit', compact('produto', 'categorias'));
    }

    public function update(Request $request, $id) 
    {
        $produto = Produto::findOrFail($id);
        
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'descrição' => 'nullable|string',
            'preço' => 'required|numeric',
            'quantidade' => 'required|integer',
            'categoria_id' => 'required|exists:categorias,id',
            'imagem' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('imagem')) {
            if ($produto->imagem && Storage::disk('public')->exists($produto->imagem)) {
                Storage::disk('public')->delete($produto->imagem);
            }
            $path = $request->file('imagem')->store('products', 'public');
            $data['imagem'] = $path;
        }

        $produto->update($data);
        return redirect()->route('produtos.index')->with('success', 'Produto atualizado!');
    }

    public function destroy($id)
    {
        $produto = Produto::findOrFail($id);
        
        if ($produto->imagem && Storage::disk('public')->exists($produto->imagem)) {
            Storage::disk('public')->delete($produto->imagem);
        }

        $produto->delete();
        return redirect()->route('produtos.index')->with('success', 'Produto removido!');
    }

    public function favorito(Request $request, $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $user = Auth::user(); 

        try {
            $user->favoritos()->toggle($id); 
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao favoritar.');
        }
    }

    public function favoritos()
    {
        if (!Auth::check()) return redirect()->route('login');

        $produtosFavoritos = Auth::user()->favoritos;
        $categorias = Categoria::all(); 
        return view('produtos.favoritos', compact('produtosFavoritos', 'categorias'));
    }

    // ADICIONADO/CORRIGIDO: Aponta para a pasta correta 'produtos.endereco'
    public function endereco($id = null)
    {
        $produto = $id ? Produto::findOrFail($id) : null;
        return view('produtos.endereco', compact('produto'));
    }
}