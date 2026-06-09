<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Cria a tabela de categorias (PS5, Xbox, etc)
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');       // Ex: PlayStation 5
            $table->string('slug');       // Ex: ps5 (usado na URL)
            $table->string('icone');      // Caminho da imagem no storage
            $table->timestamps();
        });

        // 2. Adiciona a coluna categoria_id na tabela de roupas para vincular os produtos
        Schema::table('roupas', function (Blueprint $table) {
            $table->foreignId('categoria_id')
                  ->nullable()           // Pode ser nulo se você tiver produtos sem categoria
                  ->constrained('categorias') 
                  ->onDelete('set null'); // Se apagar a categoria, o produto não é apagado
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove a chave estrangeira antes de dropar a tabela
        Schema::table('roupas', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropColumn('categoria_id');
        });

        Schema::dropIfExists('categorias');
    }
};
