<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriaController extends Controller
{
    public function create()
    {
        return view('categorias.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome',
        ]);

        Categoria::create([
            'nome' => $request->nome,
            'slug' => Str::slug($request->nome),
            'icone' => $request->icone ?? 'fas fa-tag',
        ]);

        return redirect()->route('roupas.index')->with('success', 'Console cadastrado!');
    }

    public function edit($id)
    {
        $categoria = Categoria::findOrFail($id);
        return view('categorias.edit', compact('categoria'));
    }

    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);
        
        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome,' . $id,
            'icone' => 'nullable|string|max:255',
        ]);

        $categoria->nome = $request->nome;
        $categoria->icone = $request->icone ?? $categoria->icone ?? 'fas fa-tag';
        $categoria->slug = Str::slug($request->nome);
        
        $categoria->save();

        return redirect()->route('roupas.index')->with('success', 'Console atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->roupas()->delete(); 
        $categoria->delete();

        return redirect()->route('roupas.index')->with('success', 'Console removido!');
    }
    
}