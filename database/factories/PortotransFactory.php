<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Portotrans>
 */
class PortotransFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nominal' => fake()->numberBetween(10000, 1000000),
            'porto_id' => mt_rand(1, 3),
            '' => fake()->sentence(mt_rand(2, 15)),
            'status' => fake()->randomElement(['pemasukan', 'pengeluaran']),
            // 'user_id' => mt_rand(1, 3)
        ];
    }
}
    