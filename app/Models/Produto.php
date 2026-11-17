<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Produto extends Model
{
    protected $table = 'produto';

    protected $fillable = [
        'nome',
        'descrição',
        'preço',
        'quantidade',
        'categoria_id',
        'imagem',
        'destaque',
        'ordem_destaque',
    ];

    protected function casts(): array
    {
        return [
            'destaque' => 'boolean',
        ];
    }

    // Relacionamento com Categoria
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function imagens()
    {
        return $this->hasMany(ProdutoImagem::class)->orderBy('ordem')->orderBy('id');
    }

    public function getGaleriaAttribute(): array
    {
        $capa = $this->imagem ? [Storage::url(str_replace('storage/', '', $this->imagem))] : [];

        return array_merge($capa, $this->imagens->map(fn ($img) => $img->url)->all());
    }

    public function favoritadoPor()
    {
        return $this->belongsToMany(User::class, 'favorites');
    }
}