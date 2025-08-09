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
            $table->string('start_date');
            $table->string('end_date');
            $table->string('status');
            $table->string('document_path');
            $table->string('validated_by_support');
            $table->string('support_validation_date');
            $table->string('accepted_by_tenant');
            $table->string('tenant_acceptance_date');
            $table->unsignedBigInteger('property_id')->unique();
            $table->unsignedBigInteger('user_id')->unique();
            $table->foreign('property_id')
            ->references('id')
            ->on('properties')
            ->onDelete('cascade')
            ->onUpdate('cascade');
            $table->foreign('user_id')
            ->references('id')
            ->on('users')
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
        Schema::dropIfExists('contracts');
    }
};
