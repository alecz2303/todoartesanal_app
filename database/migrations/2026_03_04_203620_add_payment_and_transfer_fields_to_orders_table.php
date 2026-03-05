<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            // Método de pago: solo necesitamos algo corto (mp|transfer)
            $table->string('payment_method', 20)->default('mp')->after('total_cents');

            // Transferencia
            $table->string('transfer_proof_path', 255)->nullable()->after('mp_status');
            $table->timestamp('transfer_submitted_at')->nullable()->after('transfer_proof_path');

            // Timestamps de negocio
            $table->timestamp('paid_at')->nullable()->after('transfer_submitted_at');
            $table->timestamp('cancelled_at')->nullable()->after('paid_at');

            // Índices útiles (ahora sí caben)
            $table->index(['status', 'payment_method']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            // IMPORTANTE: dropIndex por nombre para evitar broncas en algunos engines
            $table->dropIndex('orders_status_payment_method_index');

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