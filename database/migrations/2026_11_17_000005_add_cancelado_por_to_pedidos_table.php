<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('cancelado_por', 10)->nullable()->after('cancelamento_solicitado_em');
            $table->string('cancelado_por_nome')->nullable()->after('cancelado_por');
            $table->timestamp('cancelado_em')->nullable()->after('cancelado_por_nome');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['cancelado_por', 'cancelado_por_nome', 'cancelado_em']);
        });
    }
};
