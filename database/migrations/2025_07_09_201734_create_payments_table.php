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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
           // 1. FECHAS: Fecha de emisión y Fecha de Vencimiento
            $table->date('due_date'); // ✅ Fecha de vencimiento
            $table->date('payment_date')->nullable(); // Fecha real del pago (nulo si es pendiente)

            $table->decimal('amount', 10, 2); // ✅ Usar DECIMAL para dinero (en lugar de string)
            $table->string('status')->default('pendiente'); // Estado: pendiente, pagado, atrasado
            $table->string('payment_method')->nullable(); // Método usado (Nequi, Tarjeta, etc.)
            $table->string('receipt_path')->nullable(); // Ruta del comprobante/recibo

        // 2. RELACIÓN: Ya no es unique. Un contrato tiene MUCHOS recibos.
            $table->unsignedBigInteger('contract_id'); // ✅ QUITAMOS ->unique()
            $table->foreign('contract_id')
                ->references('id')
                ->on('contracts')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
