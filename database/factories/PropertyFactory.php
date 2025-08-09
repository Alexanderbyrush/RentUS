<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(3),
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'status' => $this->faker->randomElement(['disponible', 'ocupado', 'en mantenimiento']),
            // ✅ Ajustado para no exceder decimal(8,2)
            'monthly_price' => $this->faker->randomFloat(2, 500000, 999999),
            'area_m2' => $this->faker->numberBetween(40, 250),
            'num_bedrooms' => $this->faker->numberBetween(1, 5),
            'num_bathrooms' => $this->faker->numberBetween(1, 4),
            'included_services' => $this->faker->randomElement([
                'agua, luz',
                'agua, luz, internet',
                'todos los servicios',
                'ninguno'
            ]),
            'publication_date' => $this->faker->date(),
            'image_url' => $this->faker->imageUrl(640, 480, 'house', true, 'house'),
            'user_id' => User::factory(),
        ];
    }
}
