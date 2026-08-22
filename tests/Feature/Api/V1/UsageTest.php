<?php

use App\Models\Label;
use App\Models\Patient;
use App\Models\User;

test('practitioner can record usage of a valid label on a patient', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $patient = Patient::factory()->create([
        'first_name' => 'Marie',
        'last_name' => 'Dubois',
    ]);

    $label = Label::factory()->create([
        'code' => 'LBL-REC-USAGE-01',
        'status' => 'valid',
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/labels/'.$label->id.'/usage', [
            'patient_id' => $patient->id,
            'idempotency_key' => 'IDEMP-TEST-001',
            'procedure_type' => 'Extraction dentaire',
            'notes' => 'Utilisation sans incident.',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.label_code', 'LBL-REC-USAGE-01')
        ->assertJsonPath('data.patient_name', 'Marie Dubois')
        ->assertJsonPath('data.procedure_type', 'Extraction dentaire');

    $this->assertDatabaseHas('labels', [
        'id' => $label->id,
        'status' => 'already_used',
        'used_by_patient_name' => 'Marie Dubois',
    ]);

    $this->assertDatabaseHas('instrument_usages', [
        'label_id' => $label->id,
        'patient_id' => $patient->id,
        'idempotency_key' => 'IDEMP-TEST-001',
    ]);
});

test('recording usage is idempotent with same key', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $patient = Patient::factory()->create();
    $label = Label::factory()->create(['status' => 'valid']);

    // First request
    $response1 = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/labels/'.$label->id.'/usage', [
            'patient_id' => $patient->id,
            'idempotency_key' => 'IDEMP-DUP-001',
        ]);
    $response1->assertCreated();

    // Repeated request with same idempotency key
    $response2 = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/labels/'.$label->id.'/usage', [
            'patient_id' => $patient->id,
            'idempotency_key' => 'IDEMP-DUP-001',
        ]);

    $response2->assertOk()
        ->assertJsonPath('data.idempotency_key', 'IDEMP-DUP-001');
});

test('recording usage on recalled label is blocked with 410', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $patient = Patient::factory()->create();
    $label = Label::factory()->recalled('Sterilizer cycle failed')->create();

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/labels/'.$label->id.'/usage', [
            'patient_id' => $patient->id,
            'idempotency_key' => 'IDEMP-REC-001',
        ]);

    $response->assertStatus(410)
        ->assertJson([
            'status' => 'error',
            'message' => 'This instrument has been recalled and cannot be used.',
            'recall_reason' => 'Sterilizer cycle failed',
        ]);
});

test('recording usage on already used label is blocked with 409', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $patient = Patient::factory()->create();
    $label = Label::factory()->alreadyUsed($patient)->create();

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/labels/'.$label->id.'/usage', [
            'patient_id' => $patient->id,
            'idempotency_key' => 'IDEMP-USED-002',
        ]);

    $response->assertStatus(409)
        ->assertJson([
            'status' => 'error',
            'message' => 'This instrument has already been recorded as used.',
        ]);
});

test('practitioner can fetch usage history', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $patient = Patient::factory()->create(['first_name' => 'Élodie', 'last_name' => 'Fontaine']);
    $label = Label::factory()->create(['code' => 'LBL-HIST-01', 'product_name' => 'Sonde Dentaire']);

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/labels/'.$label->id.'/usage', [
            'patient_id' => $patient->id,
            'idempotency_key' => 'IDEMP-HIST-001',
            'procedure_type' => 'Contrôle annuel',
        ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/practitioner/usages');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'label_code',
                    'product_name',
                    'patient_name',
                    'practitioner_name',
                    'used_at',
                ],
            ],
        ]);

    expect($response->json('data.0.label_code'))->toBe('LBL-HIST-01');
});
