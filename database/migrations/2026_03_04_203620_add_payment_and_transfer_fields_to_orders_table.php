<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Método de pago
            $table->string('payment_method')->default('mp')->after('total_cents'); // mp|transfer

            // Ajustamos status default real (tu migración actual dice 'pending')
            // MVP: no cambiamos el default existente, pero sí lo usaremos desde código
            // (si quieres, puedes hacer otra migración para cambiar default)

            // Transferencia
            $table->string('transfer_proof_path')->nullable()->after('mp_status');
            $table->timestamp('transfer_submitted_at')->nullable()->after('transfer_proof_path');

            // Timestamps de negocio
            $table->timestamp('paid_at')->nullable()->after('transfer_submitted_at');
            $table->timestamp('cancelled_at')->nullable()->after('paid_at');

            // Índices útiles
            $table->index(['status', 'payment_method']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'payment_method']);

            $table->dropColumn([
                'payment_method',
                'transfer_proof_path',
                'transfer_submitted_at',
                'paid_at',
                'cancelled_at',
            ]);
        });
    }
};