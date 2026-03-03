<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('status')->default('pending'); // pending|paid|failed
            $table->unsignedInteger('total_cents')->default(0);

            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();

            $table->string('delivery')->default('pickup'); // pickup|shipping
            $table->string('address')->nullable();
            $table->string('notes')->nullable();

            $table->string('mp_preference_id')->nullable();
            $table->string('mp_payment_id')->nullable();
            $table->string('mp_status')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
