<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('metodo', 20);
            $table->string('status', 20)->default('PENDING');
            $table->unsignedInteger('total');
            $table->unsignedTinyInteger('parcelas')->nullable();
            $table->string('cartao_bandeira', 20)->nullable();
            $table->string('cartao_final', 4)->nullable();
            $table->string('gateway_id')->nullable()->index();
            $table->json('cliente')->nullable();
            $table->json('endereco')->nullable();
            $table->timestamps();
        });

        Schema::create('pedido_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->cascadeOnDelete();
            $table->foreignId('produto_id')->nullable()->constrained('produto')->nullOnDelete();
            $table->string('nome');
            $table->decimal('preco', 10, 2);
            $table->unsignedInteger('quantidade');
            $table->string('imagem')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_itens');
        Schema::dropIfExists('pedidos');
    }
};
