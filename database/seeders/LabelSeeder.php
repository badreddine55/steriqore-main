<?php

namespace Database\Seeders;

use App\Models\Cabinet;
use App\Models\InstrumentUsage;
use App\Models\Label;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cabinet = Cabinet::first() ?? Cabinet::create([
            'name' => 'Cabinet Dentaire Principal',
            'address' => '12 Rue de la Paix, 75002 Paris',
            'phone' => '+33 1 42 68 00 00',
            'email' => 'contact@cabinetdentaire.fr',
        ]);

        $patient = Patient::first();
        $user = User::where('role', 'practitioner')->first() ?? User::first();

        $labels = [
            // 🟢 VALID 1: Primary Valid Label (Curette)
            [
                'cabinet_id' => $cabinet->id,
                'code' => 'LBL-2026-001',
                'product_name' => 'Curette Gracey 1/2 Micro',
                'reference' => 'CUR-GRA-012',
                'lot_number' => 'LOT-2026-89A',
                'expiration_date' => now()->addDays(150),
                'status' => 'valid',
                'cycle_id' => 89,
                'cycle_number' => 'CYC-2026-089',
                'autoclave_name' => 'Melag Vacuklav 40B',
                'sterilization_date' => now()->subDays(3),
                'operator_name' => 'Dr. Dupont',
            ],
            // 🟢 VALID 2: Test Alias 01_VALID
            [
                'cabinet_id' => $cabinet->id,
                'code' => '01_VALID',
                'product_name' => 'Curette Gracey 1/2 Micro (QA)',
                'reference' => 'CUR-GRA-012',
                'lot_number' => 'LOT-2026-89A',
                'expiration_date' => now()->addDays(150),
                'status' => 'valid',
                'cycle_id' => 89,
                'cycle_number' => 'CYC-2026-089',
                'autoclave_name' => 'Melag Vacuklav 40B',
                'sterilization_date' => now()->subDays(3),
                'operator_name' => 'Dr. Dupont',
            ],
            // 🔴 EXPIRED 1: DLC Passed
            [
                'cabinet_id' => $cabinet->id,
                'code' => 'LBL-2026-002-EXP',
                'product_name' => 'Miroir Dentaire Front Surface #5',
                'reference' => 'MIR-FS-05',
                'lot_number' => 'LOT-2025-01X',
                'expiration_date' => now()->subDays(30),
                'status' => 'expired',
                'cycle_id' => 44,
                'cycle_number' => 'CYC-2025-044',
                'autoclave_name' => 'Melag Vacuklav 40B',
                'sterilization_date' => now()->subDays(210),
                'operator_name' => 'Dr. Dupont',
            ],
            // 🔴 EXPIRED 2: Test Alias 02_EXPIRED
            [
                'cabinet_id' => $cabinet->id,
                'code' => '02_EXPIRED',
                'product_name' => 'Miroir Dentaire Front Surface #5 (QA)',
                'reference' => 'MIR-FS-05',
                'lot_number' => 'LOT-2025-01X',
                'expiration_date' => now()->subDays(30),
                'status' => 'expired',
                'cycle_id' => 44,
                'cycle_number' => 'CYC-2025-044',
                'autoclave_name' => 'Melag Vacuklav 40B',
                'sterilization_date' => now()->subDays(210),
                'operator_name' => 'Dr. Dupont',
            ],
            // 🚨 RECALLED 1: Biological test failed
            [
                'cabinet_id' => $cabinet->id,
                'code' => 'LBL-2026-003-REC',
                'product_name' => 'Sonde Parodontale WHO 11.5',
                'reference' => 'SON-WHO-115',
                'lot_number' => 'LOT-2026-RECALL-9',
                'expiration_date' => now()->addDays(90),
                'status' => 'recalled',
                'recall_reason' => 'Biological indicator test (Geobacillus stearothermophilus) failed during cycle release.',
                'cycle_id' => 78,
                'cycle_number' => 'CYC-2026-078',
                'autoclave_name' => 'Euronda E10',
                'sterilization_date' => now()->subDays(5),
                'operator_name' => 'Dr. Martin',
            ],
            // 🚨 RECALLED 2: Test Alias 03_RECALLED
            [
                'cabinet_id' => $cabinet->id,
                'code' => '03_RECALLED',
                'product_name' => 'Sonde Parodontale WHO 11.5 (QA)',
                'reference' => 'SON-WHO-115',
                'lot_number' => 'LOT-2026-RECALL-9',
                'expiration_date' => now()->addDays(90),
                'status' => 'recalled',
                'recall_reason' => 'Biological indicator test (Geobacillus stearothermophilus) failed during cycle release.',
                'cycle_id' => 78,
                'cycle_number' => 'CYC-2026-078',
                'autoclave_name' => 'Euronda E10',
                'sterilization_date' => now()->subDays(5),
                'operator_name' => 'Dr. Martin',
            ],
            // ⚠️ ALREADY USED 1: Previously Used Instrument
            [
                'cabinet_id' => $cabinet->id,
                'code' => 'LBL-2026-004-USD',
                'product_name' => 'Implant Titane 3.5mm Grade V',
                'reference' => 'IMP-TIT-35',
                'lot_number' => 'LOT-2026-99B',
                'expiration_date' => now()->addDays(120),
                'status' => 'already_used',
                'cycle_id' => 90,
                'cycle_number' => 'CYC-2026-090',
                'autoclave_name' => 'Melag Vacuklav 40B',
                'sterilization_date' => now()->subDays(2),
                'used_at' => now()->subHours(3),
                'used_by_patient_id' => $patient?->id,
                'used_by_patient_name' => $patient?->full_name ?? 'Marie Dubois',
                'operator_name' => 'Dr. Dupont',
            ],
            // ⚠️ ALREADY USED 2: Test Alias 05_ALREADY_USED
            [
                'cabinet_id' => $cabinet->id,
                'code' => '05_ALREADY_USED',
                'product_name' => 'Implant Titane 3.5mm Grade V (QA)',
                'reference' => 'IMP-TIT-35',
                'lot_number' => 'LOT-2026-99B',
                'expiration_date' => now()->addDays(120),
                'status' => 'already_used',
                'cycle_id' => 90,
                'cycle_number' => 'CYC-2026-090',
                'autoclave_name' => 'Melag Vacuklav 40B',
                'sterilization_date' => now()->subDays(2),
                'used_at' => now()->subHours(3),
                'used_by_patient_id' => $patient?->id,
                'used_by_patient_name' => $patient?->full_name ?? 'Marie Dubois',
                'operator_name' => 'Dr. Dupont',
            ],
            // Additional Valid Instruments
            [
                'cabinet_id' => $cabinet->id,
                'code' => 'LBL-2026-005',
                'product_name' => 'Pince Gouge Friedman 14cm',
                'reference' => 'PIN-GOU-140',
                'lot_number' => 'LOT-2026-55C',
                'expiration_date' => now()->addDays(180),
                'status' => 'valid',
                'cycle_id' => 91,
                'cycle_number' => 'CYC-2026-091',
                'autoclave_name' => 'Euronda E10',
                'sterilization_date' => now()->subDays(1),
                'operator_name' => 'Dr. Martin',
            ],
            [
                'cabinet_id' => $cabinet->id,
                'code' => 'LBL-2026-006',
                'product_name' => 'Syndesmome Droit Bernard',
                'reference' => 'SYN-DRO-001',
                'lot_number' => 'LOT-2026-77D',
                'expiration_date' => now()->addDays(160),
                'status' => 'valid',
                'cycle_id' => 91,
                'cycle_number' => 'CYC-2026-091',
                'autoclave_name' => 'Euronda E10',
                'sterilization_date' => now()->subDays(1),
                'operator_name' => 'Dr. Martin',
            ],
        ];

        foreach ($labels as $labelData) {
            $label = Label::updateOrCreate(
                ['code' => $labelData['code']],
                $labelData
            );

            // If already used, also create an InstrumentUsage record
            if ($label->status === 'already_used' && $patient) {
                InstrumentUsage::firstOrCreate(
                    ['label_id' => $label->id],
                    [
                        'cabinet_id' => $cabinet->id,
                        'patient_id' => $patient->id,
                        'user_id' => $user?->id,
                        'idempotency_key' => 'SEED-USAGE-'.Str::slug($label->code),
                        'used_at' => $label->used_at ?? now()->subHours(3),
                        'procedure_type' => 'Pose Implant & Chirurgie',
                        'notes' => 'Utilisation conforme au protocole chirurgical.',
                    ]
                );
            }
        }
    }
}
