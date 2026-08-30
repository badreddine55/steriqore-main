<?php

namespace Database\Seeders;

use App\Models\Cabinet;
use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
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

        $patients = [
            [
                'cabinet_id' => $cabinet->id,
                'first_name' => 'Marie',
                'last_name' => 'Dubois',
                'dossier_id' => 'DOS-2024-001',
                'allergies' => ['Pénicilline', 'Latex'],
                'allergy_severities' => ['severe', 'moderate'],
                'last_visit' => '2026-08-10',
                'cabinet_room' => 'Cabinet 1',
                'birth_date' => '1988-04-12',
                'phone' => '+33 6 12 34 56 78',
                'email' => 'marie.dubois@example.com',
                'notes' => 'Allergie sévère à la pénicilline et intolérance au latex.',
            ],
            [
                'cabinet_id' => $cabinet->id,
                'first_name' => 'Jean',
                'last_name' => 'Moreau',
                'dossier_id' => 'DOS-2024-045',
                'allergies' => ['Ibuprofène'],
                'allergy_severities' => ['moderate'],
                'last_visit' => '2026-08-12',
                'cabinet_room' => 'Cabinet 2',
                'birth_date' => '1975-11-23',
                'phone' => '+33 6 23 45 67 89',
                'email' => 'jean.moreau@example.com',
                'notes' => 'Sensibilité aux AINS.',
            ],
            [
                'cabinet_id' => $cabinet->id,
                'first_name' => 'Sophie',
                'last_name' => 'Lefèvre',
                'dossier_id' => 'DOS-2025-112',
                'allergies' => [],
                'allergy_severities' => [],
                'last_visit' => '2026-08-14',
                'cabinet_room' => 'Salle Soins 3',
                'birth_date' => '1995-02-18',
                'phone' => '+33 6 34 56 78 90',
                'email' => 'sophie.lefevre@example.com',
                'notes' => 'Aucune allergie connue.',
            ],
            [
                'cabinet_id' => $cabinet->id,
                'first_name' => 'Pierre',
                'last_name' => 'Bernard',
                'dossier_id' => 'DOS-2023-089',
                'allergies' => ['Lidocaïne', 'Latex'],
                'allergy_severities' => ['severe', 'severe'],
                'last_visit' => '2026-08-08',
                'cabinet_room' => 'Salle Chirurgie A',
                'birth_date' => '1962-09-05',
                'phone' => '+33 6 45 67 89 01',
                'email' => 'pierre.bernard@example.com',
                'notes' => 'Attention: anesthésique local sans lidocaïne impératif.',
            ],
            [
                'cabinet_id' => $cabinet->id,
                'first_name' => 'Claire',
                'last_name' => 'Rousseau',
                'dossier_id' => 'DOS-2025-234',
                'allergies' => ['Amoxicilline'],
                'allergy_severities' => ['moderate'],
                'last_visit' => '2026-08-13',
                'cabinet_room' => 'Cabinet 1',
                'birth_date' => '1991-07-30',
                'phone' => '+33 6 56 78 90 12',
                'email' => 'claire.rousseau@example.com',
                'notes' => 'Prémédication alternative recommandée.',
            ],
            [
                'cabinet_id' => $cabinet->id,
                'first_name' => 'Marc',
                'last_name' => 'Vasseur',
                'dossier_id' => 'DOS-2026-015',
                'allergies' => ['Aspirine', 'Iode'],
                'allergy_severities' => ['mild', 'severe'],
                'last_visit' => '2026-08-15',
                'cabinet_room' => 'Box 4',
                'birth_date' => '1983-01-14',
                'phone' => '+33 6 67 89 01 23',
                'email' => 'marc.vasseur@example.com',
                'notes' => 'Traitement orthodontique en cours.',
            ],
            [
                'cabinet_id' => $cabinet->id,
                'first_name' => 'Élodie',
                'last_name' => 'Fontaine',
                'dossier_id' => 'DOS-2024-178',
                'allergies' => ['Latex'],
                'allergy_severities' => ['severe'],
                'last_visit' => '2026-08-05',
                'cabinet_room' => 'Salle Chirurgie A',
                'birth_date' => '2000-06-20',
                'phone' => '+33 6 78 90 12 34',
                'email' => 'elodie.fontaine@example.com',
                'notes' => 'Matériel sans latex obligatoire.',
            ],
            [
                'cabinet_id' => $cabinet->id,
                'first_name' => 'Lucas',
                'last_name' => 'Mercier',
                'dossier_id' => 'DOS-2026-042',
                'allergies' => [],
                'allergy_severities' => [],
                'last_visit' => '2026-08-01',
                'cabinet_room' => 'Cabinet 2',
                'birth_date' => '1998-12-03',
                'phone' => '+33 6 89 01 23 45',
                'email' => 'lucas.mercier@example.com',
                'notes' => 'Visite de contrôle annuelle.',
            ],
        ];

        foreach ($patients as $patientData) {
            Patient::updateOrCreate(
                ['dossier_id' => $patientData['dossier_id']],
                $patientData
            );
        }
    }
}
