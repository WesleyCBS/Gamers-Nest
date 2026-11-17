<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('cancelamento', 20)->nullable()->after('estoque_reservado');
            $table->string('cancelamento_motivo', 500)->nullable()->after('cancelamento');
            $table->timestamp('cancelamento_solicitado_em')->nullable()->after('cancelamento_motivo');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['cancelamento', 'cancelamento_motivo', 'cancelamento_solicitado_em']);
        });
    }
};
