<?php

use App\Models\Cabinet;
use App\Models\InstrumentUsage;
use App\Models\Label;
use App\Models\Patient;
use App\Models\User;

test('practitioner only sees patients belonging to their own cabinet', function () {
    $cabinet1 = Cabinet::factory()->create(['name' => 'Cabinet 1']);
    $cabinet2 = Cabinet::factory()->create(['name' => 'Cabinet 2']);

    $user1 = User::factory()->practitioner($cabinet1)->create();
    $token1 = $user1->createToken('token')->plainTextToken;

    $patient1 = Patient::factory()->forCabinet($cabinet1)->create(['first_name' => 'Cabinet1_Patient']);
    $patient2 = Patient::factory()->forCabinet($cabinet2)->create(['first_name' => 'Cabinet2_Patient']);

    // List patients
    $response = $this->withHeader('Authorization', 'Bearer '.$token1)
        ->getJson('/api/v1/patients');

    $response->assertOk();
    $names = collect($response->json('data'))->pluck('first_name')->all();

    expect($names)->toContain('Cabinet1_Patient')
        ->and($names)->not->toContain('Cabinet2_Patient');

    // Access patient 1 (own cabinet)
    $this->withHeader('Authorization', 'Bearer '.$token1)
        ->getJson('/api/v1/patients/'.$patient1->id)
        ->assertOk()
        ->assertJsonPath('data.first_name', 'Cabinet1_Patient');

    // ID Guessing: Access patient 2 (foreign cabinet) returns 404
    $this->withHeader('Authorization', 'Bearer '.$token1)
        ->getJson('/api/v1/patients/'.$patient2->id)
        ->assertNotFound();
});

test('practitioner cannot scan or look up labels from another cabinet', function () {
    $cabinet1 = Cabinet::factory()->create();
    $cabinet2 = Cabinet::factory()->create();

    $user1 = User::factory()->practitioner($cabinet1)->create();
    $token1 = $user1->createToken('token')->plainTextToken;

    $label1 = Label::factory()->forCabinet($cabinet1)->create(['code' => 'LBL-CABINET-1']);
    $label2 = Label::factory()->forCabinet($cabinet2)->create(['code' => 'LBL-CABINET-2']);

    // List labels
    $response = $this->withHeader('Authorization', 'Bearer '.$token1)
        ->getJson('/api/v1/labels');

    $response->assertOk();
    $codes = collect($response->json('data'))->pluck('code')->all();

    expect($codes)->toContain('LBL-CABINET-1')
        ->and($codes)->not->toContain('LBL-CABINET-2');

    // Scan label 1 (own cabinet)
    $this->withHeader('Authorization', 'Bearer '.$token1)
        ->getJson('/api/v1/labels/LBL-CABINET-1')
        ->assertOk();

    // ID Guessing: Scan label 2 (foreign cabinet) returns 404
    $this->withHeader('Authorization', 'Bearer '.$token1)
        ->getJson('/api/v1/labels/LBL-CABINET-2')
        ->assertNotFound();

    $this->withHeader('Authorization', 'Bearer '.$token1)
        ->getJson('/api/v1/labels/'.$label2->id)
        ->assertNotFound();
});

test('recording usage is blocked when attempting to use label or patient from another cabinet', function () {
    $cabinet1 = Cabinet::factory()->create();
    $cabinet2 = Cabinet::factory()->create();

    $user1 = User::factory()->practitioner($cabinet1)->create();
    $token1 = $user1->createToken('token')->plainTextToken;

    $patient1 = Patient::factory()->forCabinet($cabinet1)->create();
    $patient2 = Patient::factory()->forCabinet($cabinet2)->create();

    $label1 = Label::factory()->forCabinet($cabinet1)->create(['status' => 'valid']);
    $label2 = Label::factory()->forCabinet($cabinet2)->create(['status' => 'valid']);

    // Attempt 1: Own patient, but foreign cabinet's label -> 404
    $response1 = $this->withHeader('Authorization', 'Bearer '.$token1)
        ->postJson('/api/v1/labels/'.$label2->id.'/usage', [
            'patient_id' => $patient1->id,
            'idempotency_key' => 'IDEMP-CROSS-1',
        ]);
    $response1->assertNotFound();

    // Attempt 2: Own label, but foreign cabinet's patient -> 404
    $response2 = $this->withHeader('Authorization', 'Bearer '.$token1)
        ->postJson('/api/v1/labels/'.$label1->id.'/usage', [
            'patient_id' => $patient2->id,
            'idempotency_key' => 'IDEMP-CROSS-2',
        ]);
    $response2->assertNotFound();

    // Attempt 3: Valid usage within own cabinet -> 201 Created
    $response3 = $this->withHeader('Authorization', 'Bearer '.$token1)
        ->postJson('/api/v1/labels/'.$label1->id.'/usage', [
            'patient_id' => $patient1->id,
            'idempotency_key' => 'IDEMP-VALID-1',
        ]);
    $response3->assertCreated();
});

test('usage history is strictly scoped to the users cabinet', function () {
    $cabinet1 = Cabinet::factory()->create();
    $cabinet2 = Cabinet::factory()->create();

    $user1 = User::factory()->practitioner($cabinet1)->create();
    $user2 = User::factory()->practitioner($cabinet2)->create();

    $token1 = $user1->createToken('token')->plainTextToken;

    $patient1 = Patient::factory()->forCabinet($cabinet1)->create();
    $label1 = Label::factory()->forCabinet($cabinet1)->create();
    InstrumentUsage::factory()->forCabinet($cabinet1)->create([
        'patient_id' => $patient1->id,
        'label_id' => $label1->id,
        'user_id' => $user1->id,
    ]);

    $patient2 = Patient::factory()->forCabinet($cabinet2)->create();
    $label2 = Label::factory()->forCabinet($cabinet2)->create();
    InstrumentUsage::factory()->forCabinet($cabinet2)->create([
        'patient_id' => $patient2->id,
        'label_id' => $label2->id,
        'user_id' => $user2->id,
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$token1)
        ->getJson('/api/v1/practitioner/usages');

    $response->assertOk();
    $data = $response->json('data');

    expect($data)->toHaveCount(1)
        ->and($data[0]['label_code'])->toBe($label1->code);
});

test('auth me endpoint returns cabinet_id and role for cabinet admin', function () {
    $cabinet = Cabinet::factory()->create(['name' => 'Cabinet Monceau']);
    $admin = User::factory()->admin($cabinet)->create();
    $token = $admin->createToken('token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/user');

    $response->assertOk()
        ->assertJsonPath('data.user.role', 'admin')
        ->assertJsonPath('data.user.cabinet_id', $cabinet->id)
        ->assertJsonPath('data.user.cabinet_name', 'Cabinet Monceau')
        ->assertJsonPath('data.user.cabinet.name', 'Cabinet Monceau');
});

test('auth me endpoint returns null cabinet_id and super_admin role for super admin', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $superToken = $superAdmin->createToken('super-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$superToken)
        ->getJson('/api/v1/user');

    $response->assertOk()
        ->assertJsonPath('data.user.role', 'super_admin')
        ->assertJsonPath('data.user.cabinet_id', null);
});
