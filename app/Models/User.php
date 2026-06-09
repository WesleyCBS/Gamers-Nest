<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // RELAÇÃO DE FAVORITOS
    public function favoritos(): BelongsToMany
    {
        // 'favorites' é a tabela, 'user_id' e 'ropa_id' são as colunas
        return $this->belongsToMany(Roupa::class, 'favorites', 'user_id', 'roupa_id');
    }
}