<?php

namespace Database\Factories;

use App\Models\Maintenance;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceFactory extends Factory
{
    protected $model = Maintenance::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->sentence(12),
            'request_date' => $this->faker->date('Y-m-d'),
            'status' => $this->faker->randomElement(['pendiente', 'en proceso', 'completado']),
            'resolution_date' => $this->faker->date('Y-m-d'),
            'validated_by_tenant' => $this->faker->randomElement(['si', 'no']),
            'property_id' => Property::factory(),
            'user_id' => User::factory(),
        ];
    }
}

