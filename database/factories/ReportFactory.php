<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['financiero', 'mantenimiento', 'ocupación', 'general']),
            'applied_filter' => $this->faker->word(),
            'generation_date' => $this->faker->date('Y-m-d'),
            'user_id' => User::factory(),
        ];
    }
}

