<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria; // Importante para o Seeder achar o Model

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criando as 4 abas principais do seu sistema
        Categoria::create([
            'nome' => 'PlayStation 5',
            'slug' => 'ps5',
            'icone' => 'categorias/ps5.png'
        ]);

        Categoria::create([
            'nome' => 'Xbox Series',
            'slug' => 'xbox',
            'icone' => 'categorias/xbox.png'
        ]);

        Categoria::create([
            'nome' => 'Nintendo Switch',
            'slug' => 'switch',
            'icone' => 'categorias/switch.png'
        ]);

        Categoria::create([
            'nome' => 'PC Gamer',
            'slug' => 'pc',
            'icone' => 'categorias/pc.png'
        ]);
    }
}
