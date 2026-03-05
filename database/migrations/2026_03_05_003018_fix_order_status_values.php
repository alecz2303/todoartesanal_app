<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Si antes usabas "pending" genérico, lo pasamos a pending_payment por defecto
        DB::table('orders')
            ->where('status', 'pending')
            ->update(['status' => 'pending_payment']);

        // (Opcional) Si existieran vacíos
        DB::table('orders')
            ->whereNull('status')
            ->update(['status' => 'pending_payment']);
    }

    public function down(): void
    {
        // Reversa simple (opcional)
        DB::table('orders')
            ->where('status', 'pending_payment')
            ->update(['status' => 'pending']);
    }
};