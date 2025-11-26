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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('address');
            $table->string('city');
            $table->string('status');
            $table->decimal('monthly_price');
            $table->integer('area_m2');
            $table->string('num_bedrooms');
            $table->string('num_bathrooms');
            $table->string('included_services');
            $table->date('publication_date');
            $table->text('image_url');
<<<<<<< HEAD
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
=======
            $table->string('lat');
            $table->string('lng');
>>>>>>> 3657076c5f81cc99851e4fe4bb519c2eddeb1c2b
            $table->unsignedBigInteger('user_id');
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
        Schema::dropIfExists('properties');
    }
};
