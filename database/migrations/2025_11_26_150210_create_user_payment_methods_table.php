<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_payment_methods', function (Blueprint $table) {
            $table->id();
            // 1. RELACIÓN CON EL USUARIO (Cliente)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // 2. TIPO DE MÉTODO (Tarjeta, Nequi, PSE, etc.)
            $table->string('type'); // 'tarjeta', 'nequi', 'bancolombia', 'pse', 'daviplata'

            // 3. IDENTIFICADOR ÚNICO / METADATOS (Simulación de Token)
            $table->string('identifier')->nullable(); // Ej: Número de teléfono Nequi, o un token de tarjeta.
            $table->string('last_four_digits')->nullable(); // Útiles para tarjetas.
            $table->string('card_brand')->nullable(); // Visa, Mastercard, etc.

            // 4. ESTADO
            $table->boolean('is_default')->default(false); // Método predeterminado.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_payment_methods');
    }
};
