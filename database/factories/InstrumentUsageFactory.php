<?php

namespace Database\Factories;

use App\Models\InstrumentUsage;
use App\Models\Label;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<InstrumentUsage>
 */
class InstrumentUsageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<InstrumentUsage>
     */
    protected $model = InstrumentUsage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label_id' => Label::factory(),
            'patient_id' => Patient::factory(),
            'user_id' => User::factory(),
            'idempotency_key' => (string) Str::uuid(),
            'used_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'procedure_type' => fake()->randomElement(['Détartrage & Surfaçage', 'Pose Implant', 'Extraction molaire', 'Soins Carie', 'Chirurgie Parodontale']),
            'notes' => fake()->optional(0.5)->sentence(),
        ];
    }
}
