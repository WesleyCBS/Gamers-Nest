<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produto_imagens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produto')->cascadeOnDelete();
            $table->string('caminho');
            $table->unsignedTinyInteger('ordem')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_imagens');
    }
};
