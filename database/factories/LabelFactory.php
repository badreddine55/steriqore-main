<?php

namespace Database\Factories;

use App\Models\Label;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Label>
 */
class LabelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Label>
     */
    protected $model = Label::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $instruments = [
            ['name' => 'Curette Gracey 1/2 Micro', 'ref' => 'CUR-GRA-012'],
            ['name' => 'Miroir Dentaire Front Surface #5', 'ref' => 'MIR-FS-05'],
            ['name' => 'Sonde Parodontale WHO 11.5', 'ref' => 'SON-WHO-115'],
            ['name' => 'Implant Titane 3.5mm Grade V', 'ref' => 'IMP-TIT-35'],
            ['name' => 'Pince Gouge Friedman 14cm', 'ref' => 'PIN-GOU-140'],
            ['name' => 'Syndesmome Droit Bernard', 'ref' => 'SYN-DRO-001'],
            ['name' => 'Porte-Aiguille Castroviejo 14cm', 'ref' => 'POR-AIG-140'],
        ];

        $instrument = fake()->randomElement($instruments);
        $cycleNum = fake()->numberBetween(50, 120);

        return [
            'code' => 'LBL-'.fake()->numberBetween(2025, 2026).'-'.fake()->unique()->numerify('####'),
            'product_name' => $instrument['name'],
            'reference' => $instrument['ref'],
            'lot_number' => 'LOT-'.fake()->numberBetween(2025, 2026).'-'.fake()->bothify('##?'),
            'expiration_date' => fake()->dateTimeBetween('+1 month', '+6 months'),
            'status' => 'valid',
            'cycle_id' => $cycleNum,
            'cycle_number' => 'CYC-2026-'.str_pad((string) $cycleNum, 3, '0', STR_PAD_LEFT),
            'autoclave_name' => fake()->randomElement(['Melag Vacuklav 40B', 'Euronda E10', 'W&H Lisa 500']),
            'sterilization_date' => fake()->dateTimeBetween('-2 months', '-1 days'),
            'recall_reason' => null,
            'used_at' => null,
            'used_by_patient_id' => null,
            'used_by_patient_name' => null,
            'operator_name' => fake()->randomElement(['Dr. Dupont', 'Dr. Martin', 'Dr. Moreau', 'Assistante Sarah']),
        ];
    }

    /**
     * Indicate that the label is expired.
     */
    public function expired(int $daysAgo = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expiration_date' => now()->subDays($daysAgo),
            'sterilization_date' => now()->subDays($daysAgo + 180),
        ]);
    }

    /**
     * Indicate that the label is recalled.
     */
    public function recalled(string $reason = 'Biological indicator test failed during cycle release.'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'recalled',
            'recall_reason' => $reason,
        ]);
    }

    /**
     * Indicate that the label was already used.
     */
    public function alreadyUsed(?Patient $patient = null, ?\DateTimeInterface $usedAt = null): static
    {
        return $this->state(function (array $attributes) use ($patient, $usedAt) {
            $patientInstance = $patient ?? Patient::first() ?? Patient::factory()->create();

            return [
                'status' => 'already_used',
                'used_at' => $usedAt ?? now()->subHours(4),
                'used_by_patient_id' => $patientInstance->id,
                'used_by_patient_name' => $patientInstance->full_name,
            ];
        });
    }
}
