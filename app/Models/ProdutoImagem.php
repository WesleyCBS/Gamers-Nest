<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProdutoImagem extends Model
{
    protected $table = 'produto_imagens';

    protected $fillable = [
        'produto_id',
        'caminho',
        'ordem',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->caminho);
    }
}
