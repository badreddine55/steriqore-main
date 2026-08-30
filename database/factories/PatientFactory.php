<?php

namespace Database\Factories;

use App\Models\Cabinet;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Patient>
     */
    protected $model = Patient::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasAllergies = fake()->boolean(60);
        $commonAllergies = ['Latex', 'Pénicilline', 'Amoxicilline', 'Ibuprofène', 'Lidocaïne', 'Aspirine', 'Iode'];
        $severities = ['mild', 'moderate', 'severe'];

        $allergies = [];
        $allergySeverities = [];

        if ($hasAllergies) {
            $count = fake()->numberBetween(1, 2);
            $selected = fake()->randomElements($commonAllergies, $count);
            foreach ($selected as $allergy) {
                $allergies[] = $allergy;
                $allergySeverities[] = fake()->randomElement($severities);
            }
        }

        return [
            'cabinet_id' => fn () => Cabinet::first()?->id ?? Cabinet::factory()->create()->id,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'dossier_id' => 'DOS-'.fake()->numberBetween(2023, 2026).'-'.fake()->unique()->numerify('###'),
            'allergies' => $allergies,
            'allergy_severities' => $allergySeverities,
            'last_visit' => fake()->optional(0.9)->dateTimeBetween('-6 months', 'now')?->format('Y-m-d'),
            'cabinet_room' => fake()->optional(0.8)->randomElement(['Cabinet 1', 'Cabinet 2', 'Salle Chirurgie A', 'Salle Soins 3', 'Box 4']),
            'birth_date' => fake()->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'notes' => fake()->optional(0.4)->sentence(),
        ];
    }

    /**
     * Link the patient to a specific cabinet.
     */
    public function forCabinet(Cabinet $cabinet): static
    {
        return $this->state(fn (array $attributes) => [
            'cabinet_id' => $cabinet->id,
        ]);
    }

    /**
     * Indicate that the patient has no allergies.
     */
    public function noAllergies(): static
    {
        return $this->state(fn (array $attributes) => [
            'allergies' => [],
            'allergy_severities' => [],
        ]);
    }

    /**
     * Indicate that the patient has a severe latex allergy.
     */
    public function withLatexAllergy(string $severity = 'severe'): static
    {
        return $this->state(fn (array $attributes) => [
            'allergies' => array_values(array_unique(array_merge(['Latex'], $attributes['allergies'] ?? []))),
            'allergy_severities' => array_values(array_merge([$severity], $attributes['allergy_severities'] ?? [])),
        ]);
    }

    /**
     * Indicate that the patient has a penicillin allergy.
     */
    public function withPenicillinAllergy(string $severity = 'severe'): static
    {
        return $this->state(fn (array $attributes) => [
            'allergies' => array_values(array_unique(array_merge(['Pénicilline'], $attributes['allergies'] ?? []))),
            'allergy_severities' => array_values(array_merge([$severity], $attributes['allergy_severities'] ?? [])),
        ]);
    }
}
