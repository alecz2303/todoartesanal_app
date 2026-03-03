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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();

            $table->text('description')->nullable();
            $table->string('dimensions')->nullable(); // ej: "50cm alto x 40cm ancho"

            $table->unsignedInteger('price_cents'); // MXN en centavos
            $table->unsignedInteger('stock')->nullable(); // null = sin control
            $table->boolean('is_active')->default(true);

            $table->string('cover_image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
