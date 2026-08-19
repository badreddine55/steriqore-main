<?php

use App\Models\Patient;
use App\Models\User;
use Database\Seeders\PatientSeeder;

test('unauthenticated user cannot access patients endpoint', function () {
    $response = $this->getJson('/api/v1/patients');

    $response->assertUnauthorized();
});

test('authenticated practitioner can list all patients', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    Patient::factory()->count(3)->create();

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/patients');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'first_name',
                    'last_name',
                    'full_name',
                    'dossier_id',
                    'allergies',
                    'allergy_severities',
                    'last_visit',
                    'cabinet_room',
                ],
            ],
        ]);

    expect(count($response->json('data')))->toBeGreaterThanOrEqual(3);
});

test('authenticated practitioner can search patients by first name, last name, or dossier id', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    Patient::factory()->create([
        'first_name' => 'Alexander',
        'last_name' => 'Fleming',
        'dossier_id' => 'DOS-SEARCH-001',
    ]);

    Patient::factory()->create([
        'first_name' => 'Marie',
        'last_name' => 'Curie',
        'dossier_id' => 'DOS-SEARCH-002',
    ]);

    // Search by first name
    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/patients?search=Alexander');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1)
        ->and($data[0]['first_name'])->toBe('Alexander');

    // Search by last name
    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/patients?search=Curie');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1)
        ->and($data[0]['last_name'])->toBe('Curie');

    // Search by dossier ID
    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/patients?search=DOS-SEARCH-001');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(1)
        ->and($data[0]['dossier_id'])->toBe('DOS-SEARCH-001');
});

test('authenticated practitioner can retrieve a single patient', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $patient = Patient::factory()->withLatexAllergy('severe')->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'dossier_id' => 'DOS-SINGLE-01',
        'cabinet_room' => 'Cabinet 1',
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/patients/'.$patient->id);

    $response->assertOk()
        ->assertJsonPath('data.id', (string) $patient->id)
        ->assertJsonPath('data.first_name', 'John')
        ->assertJsonPath('data.last_name', 'Doe')
        ->assertJsonPath('data.dossier_id', 'DOS-SINGLE-01')
        ->assertJsonPath('data.cabinet_room', 'Cabinet 1');

    expect($response->json('data.allergies'))->toContain('Latex');
});

test('patient seeder populates predefined clinical patients', function () {
    $this->seed(PatientSeeder::class);

    $this->assertDatabaseHas('patients', [
        'first_name' => 'Marie',
        'last_name' => 'Dubois',
        'dossier_id' => 'DOS-2024-001',
    ]);

    $this->assertDatabaseHas('patients', [
        'first_name' => 'Pierre',
        'last_name' => 'Bernard',
        'dossier_id' => 'DOS-2023-089',
    ]);
});
