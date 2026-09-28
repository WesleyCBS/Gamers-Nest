<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $table = 'produto';

    protected $fillable = [
        'nome',
        'descrição',
        'preço',
        'quantidade',
        'categoria_id',
        'imagem'
    ];

    // Relacionamento com Categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function favoritadoPor()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }
}