<?php

namespace Database\Factories;

use App\Models\Cabinet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cabinet>
 */
class CabinetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Cabinet>
     */
    protected $model = Cabinet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Cabinet Dentaire '.fake()->city(),
            'address' => fake()->streetAddress().', '.fake()->postcode().' '.fake()->city(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
        ];
    }
}
