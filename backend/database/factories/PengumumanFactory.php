<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PengumumanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul' => $this->faker->sentence(10),
            'isi' => $this->faker->paragraph(5),
            'file' => null,
            'kategori' => 'Pengumuman',
            'user_id' => 1,
            'created_at' => $this->faker->dateTimeBetween('-2 month', 'now'),
        ];
    }
}
