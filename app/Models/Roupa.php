<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roupa extends Model
{
    protected $fillable = [
        'nome',
        'descrição',
        'preço',
        'quantidade',
        'categoria_id', // <--- ESSA LINHA É A CHAVE! Agora o Laravel permite salvar a categoria.
        'imagem'
    ];

    // Relacionamento com Categoria (Bom ter para o filtro funcionar 100%)
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function favoritadoPor()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }
}