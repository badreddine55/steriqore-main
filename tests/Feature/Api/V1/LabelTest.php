<?php

use App\Models\Label;
use App\Models\User;
use Database\Seeders\LabelSeeder;
use Database\Seeders\PatientSeeder;

test('unauthenticated request to labels is rejected', function () {
    $response = $this->getJson('/api/v1/labels/LBL-2026-001');

    $response->assertUnauthorized();
});

test('authenticated practitioner can scan a valid label by code', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $label = Label::factory()->create([
        'code' => 'LBL-TEST-VALID-01',
        'product_name' => 'Curette Gracey 1/2 Micro',
        'status' => 'valid',
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/labels/LBL-TEST-VALID-01');

    $response->assertOk()
        ->assertJsonPath('data.code', 'LBL-TEST-VALID-01')
        ->assertJsonPath('data.product_name', 'Curette Gracey 1/2 Micro')
        ->assertJsonPath('data.status', 'valid');
});

test('scanning an unknown label code returns 404', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/labels/UNKNOWN-CODE-999');

    $response->assertNotFound()
        ->assertJson([
            'status' => 'error',
            'message' => 'Item not found. Please check the code and try again.',
        ]);
});

test('scanning a recalled label returns recall reason', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $label = Label::factory()->recalled('Sterilizer cycle temperature drop below 134C.')->create([
        'code' => 'LBL-TEST-RECALLED-01',
    ]);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/labels/LBL-TEST-RECALLED-01');

    $response->assertOk()
        ->assertJsonPath('data.status', 'recalled')
        ->assertJsonPath('data.recall_reason', 'Sterilizer cycle temperature drop below 134C.');
});

test('label seeder seeds all 4 status variants correctly', function () {
    $this->seed(PatientSeeder::class);
    $this->seed(LabelSeeder::class);

    $this->assertDatabaseHas('labels', [
        'code' => 'LBL-2026-001',
        'status' => 'valid',
    ]);

    $this->assertDatabaseHas('labels', [
        'code' => 'LBL-2026-002-EXP',
        'status' => 'expired',
    ]);

    $this->assertDatabaseHas('labels', [
        'code' => 'LBL-2026-003-REC',
        'status' => 'recalled',
    ]);

    $this->assertDatabaseHas('labels', [
        'code' => 'LBL-2026-004-USD',
        'status' => 'already_used',
    ]);
});
