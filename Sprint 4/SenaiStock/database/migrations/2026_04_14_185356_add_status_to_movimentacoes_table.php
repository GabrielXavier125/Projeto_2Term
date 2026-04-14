<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expand tipo ENUM to include 'reserva'
        DB::statement("ALTER TABLE movimentacoes MODIFY tipo ENUM('entrada', 'saida', 'reserva') NOT NULL");

        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->enum('status', ['pendente', 'confirmada'])->default('confirmada')->after('tipo');
            $table->foreignId('confirmado_por')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->timestamp('confirmado_at')->nullable()->after('confirmado_por');
        });
    }

    public function down(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->dropForeign(['confirmado_por']);
            $table->dropColumn(['status', 'confirmado_por', 'confirmado_at']);
        });

        DB::statement("ALTER TABLE movimentacoes MODIFY tipo ENUM('entrada', 'saida') NOT NULL");
    }
};
