<?php

namespace Database\Factories;

use App\Models\Rating;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingFactory extends Factory
{
    protected $model = Rating::class;

    public function definition(): array
    {
        return [
            'recipient_role' => $this->faker->randomElement(['arrendatario', 'propietario', 'soporte']),
            'score' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->sentence(10),
            'date' => $this->faker->date('Y-m-d'),
            'contract_id' => Contract::factory(),
            'user_id' => User::factory(),
        ];
    }
}

