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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status');
            $table->string('document_path')->nullable();
            $table->boolean('validated_by_support')->default(false);
            $table->timestamp('support_validation_date')->nullable();
            $table->boolean('accepted_by_tenant')->default(false);
            $table->timestamp('tenant_acceptance_date')->nullable();
            $table->unsignedBigInteger('property_id')->unique();
            $table->foreign('property_id')
                ->references('id')
                ->on('properties')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->unsignedBigInteger('landlord_id'); // arrendador
            $table->unsignedBigInteger('tenant_id');   // arrendatario

            $table->foreign('landlord_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('tenant_id')->references('id')->on('users')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
