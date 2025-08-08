<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario de ejemplo fijo
        User::create([
            'name' => 'Usuario Ejemplo',
            'phone' => '3000000000',
            'email' => 'usuario@example.com',
            'password_hash' => 'hash_generico_123',
            'address' => 'Calle Falsa 123',
            'id_documento' => '1234567890',
            'status' => 'active',
            'email_verified_at' => now(),
            'password' => 'clave_generica',
            'remember_token' => 'token12345',
        ]);

        // Usuarios aleatorios
        User::factory(10)->create();
    }
}
