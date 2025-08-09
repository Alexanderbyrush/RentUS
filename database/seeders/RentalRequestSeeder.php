<?php

namespace Database\Seeders;

use App\Models\RentalRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RentalRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RentalRequest::factory()->count(20)->create();
    }
}
