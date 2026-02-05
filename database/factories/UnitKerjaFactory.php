<?php

namespace Database\Factories;

use App\Models\UnitKerja;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UnitKerja>
 */
class UnitKerjaFactory extends Factory
{
    protected $model = UnitKerja::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->company() . ' ' . fake()->randomElement(['Kota', 'Kabupaten', 'Provinsi']),
            'tipe' => fake()->randomElement(UnitKerja::tipeOptions()),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the unit kerja is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set specific type for testing.
     */
    public function indukPerangkatDaerah(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipe' => UnitKerja::TIPE_INDUK,
        ]);
    }
}
