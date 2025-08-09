<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition(): array
    {
        return [
            'start_date' => $this->faker->date('Y-m-d'),
            'end_date' => $this->faker->date('Y-m-d', '+1 year'),
            'status' => $this->faker->randomElement(['activo', 'finalizado', 'pendiente']),
            'document_path' => $this->faker->filePath(),
            'validated_by_support' => $this->faker->randomElement(['si', 'no']),
            'support_validation_date' => $this->faker->date('Y-m-d'),
            'accepted_by_tenant' => $this->faker->randomElement(['si', 'no']),
            'tenant_acceptance_date' => $this->faker->date('Y-m-d'),
            'property_id' => Property::factory(),
            'user_id' => User::factory(),
        ];
    }
}
