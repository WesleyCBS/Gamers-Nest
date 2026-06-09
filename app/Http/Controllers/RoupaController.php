<?php

namespace App\Http\Controllers;

use App\Models\Roupa;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RoupaController extends Controller
{
    // 1. LISTAR TUDO (Busca global em todos os itens)
    public function index(Request $request)
    {
        $query = Roupa::with('categoria');

        // Lógica de busca global
        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nome', 'LIKE', "%{$search}%")
                  ->orWhere('descrição', 'LIKE', "%{$search}%");
            });
        }

        $roupas = $query->paginate(10)->withQueryString();
        $categorias = Categoria::all(); 
        return view('roupas.index', compact('roupas', 'categorias'));
    }

    // 2. FILTRAR POR CONSOLE (Busca restrita à categoria selecionada)
    public function porCategoria(Request $request, $slug)
    {
        $categoriaAtiva = Categoria::where('slug', $slug)->firstOrFail();
        
        // Inicia a query filtrando pela categoria da aba
        $query = Roupa::where('categoria_id', $categoriaAtiva->id);

        // Se houver pesquisa, filtra apenas DENTRO dessa categoria
        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nome', 'LIKE', "%{$search}%")
                  ->orWhere('descrição', 'LIKE', "%{$search}%");
            });
        }
        
        $roupas = $query->paginate(10)->withQueryString();
        $categorias = Categoria::all(); 

        return view('roupas.index', compact('roupas', 'categorias', 'categoriaAtiva'));
    }

    // 3. DETALHES DO PRODUTO
    public function show($id) 
    {
        $roupa = Roupa::with('categoria')->findOrFail($id);
        return view('roupas.show', compact('roupa'));
    }

    // 4. FORMULÁRIO DE CRIAÇÃO
    public function create()
    {
        $categorias = Categoria::all(); 
        return view('roupas.create', compact('categorias'));
    }

    // 5. SALVAR PRODUTO (Salva no storage/app/public padrão)
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

        Roupa::create($data);
        return redirect()->route('roupas.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    // 6. EDITAR PRODUTO
    public function edit($id) 
    {
        $roupa = Roupa::findOrFail($id);
        $categorias = Categoria::all();
        return view('roupas.edit', compact('roupa', 'categorias'));
    }

    // 7. ATUALIZAR PRODUTO (Atualiza no storage padrão)
    public function update(Request $request, $id) 
    {
        $roupa = Roupa::findOrFail($id);
        
        $data = $request->validate([
            'nome' => 'required|string|max:255',
            'descrição' => 'nullable|string',
            'preço' => 'required|numeric',
            'quantidade' => 'required|integer',
            'categoria_id' => 'required|exists:categorias,id',
            'imagem' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('imagem')) {
            if ($roupa->imagem && Storage::disk('public')->exists($roupa->imagem)) {
                Storage::disk('public')->delete($roupa->imagem);
            }
            $path = $request->file('imagem')->store('products', 'public');
            $data['imagem'] = $path;
        }

        $roupa->update($data);
        return redirect()->route('roupas.index')->with('success', 'Produto atualizado!');
    }

    // 8. EXCLUIR PRODUTO
    public function destroy($id)
    {
        $roupa = Roupa::findOrFail($id);
        
        if ($roupa->imagem && Storage::disk('public')->exists($roupa->imagem)) {
            Storage::disk('public')->delete($roupa->imagem);
        }

        $roupa->delete();
        return redirect()->route('roupas.index')->with('success', 'Produto removido!');
    }

    // 9. LÓGICA DE FAVORITAR
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

    // 10. LISTAR FAVORITOS
    public function favoritos()
    {
        if (!Auth::check()) return redirect()->route('login');

        $roupasFavoritas = Auth::user()->favoritos;
        $categorias = Categoria::all(); 
        return view('roupas.favoritos', compact('roupasFavoritas', 'categorias'));
    }
}