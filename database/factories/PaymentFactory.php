<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'payment_date' => $this->faker->date('Y-m-d'),
            'amount' => $this->faker->randomFloat(2, 500000, 999999),
            'status' => $this->faker->randomElement(['pagado', 'pendiente', 'atrasado']),
            'payment_method' => $this->faker->randomElement(['efectivo', 'transferencia', 'tarjeta']),
            // ✅ Ajustado: ahora genera una URL simulando un comprobante
            'receipt_path' => $this->faker->imageUrl(640, 480, 'business', true, 'receipt'),
            'contract_id' => Contract::factory(),
        ];
    }
}
