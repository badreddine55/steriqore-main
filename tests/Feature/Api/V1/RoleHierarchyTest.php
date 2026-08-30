<?php

use App\Models\Cabinet;
use App\Models\User;

test('super admin gets 403 on every cabinet scoped endpoint', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $token = $superAdmin->createToken('test-device')->plainTextToken;

    $headers = ['Authorization' => 'Bearer '.$token];

    // 1. Alerts
    $this->withHeaders($headers)->getJson('/api/v1/alerts')->assertStatus(403);

    // 2. Stock levels
    $this->withHeaders($headers)->getJson('/api/v1/stock-levels')->assertStatus(403);

    // 3. Cycles
    $this->withHeaders($headers)->getJson('/api/v1/cycles')->assertStatus(403);
    $this->withHeaders($headers)->getJson('/api/v1/cycles/89')->assertStatus(403);
    $this->withHeaders($headers)->getJson('/api/v1/cycles/89/items')->assertStatus(403);
    $this->withHeaders($headers)->getJson('/api/v1/cycles/89/attachments')->assertStatus(403);

    // 4. Patients
    $this->withHeaders($headers)->getJson('/api/v1/patients')->assertStatus(403);
    $this->withHeaders($headers)->getJson('/api/v1/patients/1')->assertStatus(403);

    // 5. Labels
    $this->withHeaders($headers)->getJson('/api/v1/labels')->assertStatus(403);
    $this->withHeaders($headers)->getJson('/api/v1/labels/LBL-001')->assertStatus(403);

    // 6. Usages
    $this->withHeaders($headers)->postJson('/api/v1/labels/1/usage', [
        'patient_id' => 1,
        'idempotency_key' => 'key-123',
    ])->assertStatus(403);

    $this->withHeaders($headers)->getJson('/api/v1/practitioner/usages')->assertStatus(403);
    $this->withHeaders($headers)->getJson('/api/v1/usages')->assertStatus(403);
});

test('super admin can only list admin users and not staff', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $token = $superAdmin->createToken('test-device')->plainTextToken;

    $cabinet1 = Cabinet::factory()->create();
    $admin1 = User::factory()->admin($cabinet1)->create(['name' => 'Admin One', 'email' => 'admin1@test.com']);
    $staff1 = User::factory()->practitioner($cabinet1)->create(['name' => 'Dr. Staff', 'email' => 'staff1@test.com']);
    $stockMgr = User::factory()->stockManager($cabinet1)->create(['name' => 'Stock Manager', 'email' => 'stock1@test.com']);

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/users');

    $response->assertOk();
    $data = $response->json('data');

    $emails = collect($data)->pluck('email')->all();
    expect($emails)->toContain('admin1@test.com')
        ->and($emails)->not->toContain('staff1@test.com')
        ->and($emails)->not->toContain('stock1@test.com');
});

test('super admin creating an admin auto provisions a cabinet', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $token = $superAdmin->createToken('test-device')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/users', [
            'name' => 'Dr. Jean Dupont',
            'email' => 'jean.dupont@cabinet-etoile.com',
            'password' => 'SecurePass123!',
            'role' => 'admin',
            'cabinet_name' => 'Cabinet Dentaire de l\'Étoile',
            'cabinet_address' => '50 Avenue des Champs-Élysées, 75008 Paris',
            'cabinet_phone' => '+33 1 50 00 00 00',
            'cabinet_email' => 'contact@cabinet-etoile.com',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.role', 'admin')
        ->assertJsonPath('data.name', 'Dr. Jean Dupont')
        ->assertJsonPath('data.cabinet_name', 'Cabinet Dentaire de l\'Étoile')
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'role',
                'cabinet_id',
                'cabinet' => ['id', 'name', 'address', 'phone', 'email'],
            ],
        ]);

    $newAdmin = User::where('email', 'jean.dupont@cabinet-etoile.com')->first();
    expect($newAdmin)->not->toBeNull()
        ->and($newAdmin->cabinet_id)->not->toBeNull()
        ->and($newAdmin->cabinet->name)->toBe('Cabinet Dentaire de l\'Étoile')
        ->and($newAdmin->cabinet->address)->toBe('50 Avenue des Champs-Élysées, 75008 Paris');
});

test('super admin cannot create staff roles directly', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $token = $superAdmin->createToken('test-device')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/users', [
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'role' => 'practitioner',
            'password' => 'password123',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

test('admin can only list staff users belonging to their own cabinet', function () {
    $cabinet1 = Cabinet::factory()->create(['name' => 'Cabinet Alpha']);
    $cabinet2 = Cabinet::factory()->create(['name' => 'Cabinet Beta']);

    $admin1 = User::factory()->admin($cabinet1)->create();
    $staff1 = User::factory()->practitioner($cabinet1)->create(['email' => 'staff.alpha@example.com']);
    $stock1 = User::factory()->stockManager($cabinet1)->create(['email' => 'stock.alpha@example.com']);

    $admin2 = User::factory()->admin($cabinet2)->create(['email' => 'admin.beta@example.com']);
    $staff2 = User::factory()->practitioner($cabinet2)->create(['email' => 'staff.beta@example.com']);

    $token = $admin1->createToken('admin-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/users');

    $response->assertOk();
    $emails = collect($response->json('data'))->pluck('email')->all();

    expect($emails)->toContain('staff.alpha@example.com')
        ->and($emails)->toContain('stock.alpha@example.com')
        ->and($emails)->not->toContain('admin.beta@example.com')
        ->and($emails)->not->toContain('staff.beta@example.com')
        ->and($emails)->not->toContain($admin1->email);
});

test('admin creating staff user assigns them to admin cabinet', function () {
    $cabinet = Cabinet::factory()->create(['name' => 'Cabinet Pasteur']);
    $admin = User::factory()->admin($cabinet)->create();
    $token = $admin->createToken('admin-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/users', [
            'name' => 'Dr. Robert Koch',
            'email' => 'robert.koch@pasteur.com',
            'role' => 'practitioner',
            'cabinet_room' => 'Fauteuil Chirurgie',
            'password' => 'password123',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.cabinet_id', $cabinet->id)
        ->assertJsonPath('data.role', 'practitioner');

    $this->assertDatabaseHas('users', [
        'email' => 'robert.koch@pasteur.com',
        'cabinet_id' => $cabinet->id,
        'role' => 'practitioner',
    ]);
});

test('admin cannot create another admin or super admin', function () {
    $cabinet = Cabinet::factory()->create();
    $admin = User::factory()->admin($cabinet)->create();
    $token = $admin->createToken('admin-token')->plainTextToken;

    // Attempt to create another admin
    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/users', [
            'name' => 'Rogue Admin',
            'email' => 'rogue@example.com',
            'role' => 'admin',
            'password' => 'password123',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

test('admin cannot view or modify staff of another cabinet', function () {
    $cabinet1 = Cabinet::factory()->create();
    $cabinet2 = Cabinet::factory()->create();

    $admin1 = User::factory()->admin($cabinet1)->create();
    $staff2 = User::factory()->practitioner($cabinet2)->create();

    $token = $admin1->createToken('admin-token')->plainTextToken;

    // GET other cabinet's staff
    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/users/'.$staff2->id)
        ->assertStatus(403);

    // PUT other cabinet's staff
    $this->withHeader('Authorization', 'Bearer '.$token)
        ->putJson('/api/v1/users/'.$staff2->id, ['name' => 'Hacked Name'])
        ->assertStatus(403);

    // DELETE other cabinet's staff
    $this->withHeader('Authorization', 'Bearer '.$token)
        ->deleteJson('/api/v1/users/'.$staff2->id)
        ->assertStatus(403);
});

test('staff members cannot access user management endpoints', function () {
    $cabinet = Cabinet::factory()->create();
    $practitioner = User::factory()->practitioner($cabinet)->create();
    $token = $practitioner->createToken('token')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/users')
        ->assertStatus(403);

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/users', [
            'name' => 'New User',
            'email' => 'new@example.com',
            'role' => 'assistant',
        ])
        ->assertStatus(403);
});
