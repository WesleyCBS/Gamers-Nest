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
            // Removi a validação do ícone aqui pois ele não vem do formulário
        ]);

        Categoria::create([
            'nome' => $request->nome,
            'slug' => Str::slug($request->nome),
            // Aqui está a solução: se o icone for nulo, ele salva 'fas fa-tag'
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
            'icone' => 'nullable|string|max:255', // Deixei nullable para não dar erro se não enviar
        ]);

        $categoria->nome = $request->nome;
        // No update, se não enviar ícone, mantemos o que já estava ou usamos o padrão
        $categoria->icone = $request->icone ?? $categoria->icone ?? 'fas fa-tag';
        $categoria->slug = Str::slug($request->nome);
        
        $categoria->save();

        return redirect()->route('roupas.index')->with('success', 'Console atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        
        // Remove os produtos vinculados para evitar erro de chave estrangeira
        $categoria->roupas()->delete(); 
        $categoria->delete();

        return redirect()->route('roupas.index')->with('success', 'Console removido!');
    }
    
}