<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class DosenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_lengkap' => $this->faker->firstName() . ' ' . $this->faker->lastName(),
            'keahlian_rekognisi' => $this->faker->sentence(5),
            'email' => $this->faker->email(),
            'external_link' => $this->faker->url(),
            'photo' => null,
        ];
    }
}
