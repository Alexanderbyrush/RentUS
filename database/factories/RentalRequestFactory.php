<?php

namespace Database\Factories;

use App\Models\RentalRequest;
use App\Models\Contract;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentalRequestFactory extends Factory
{
    protected $model = RentalRequest::class;

    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'property_id' => Property::factory(),
            'user_id' => User::factory(),
        ];
    }
}
