<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produto', function (Blueprint $table) {
            $table->boolean('destaque')->default(false)->after('imagem');
            $table->unsignedInteger('ordem_destaque')->nullable()->after('destaque');
        });
    }

    public function down(): void
    {
        Schema::table('produto', function (Blueprint $table) {
            $table->dropColumn(['destaque', 'ordem_destaque']);
        });
    }
};
