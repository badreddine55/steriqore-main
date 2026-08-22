<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('api v1 status endpoint returns operational response', function () {
    $response = $this->getJson('/api/v1');

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'message' => 'Steriqore Mobile API v1 is operational',
            'version' => '1.0.0',
        ])
        ->assertJsonStructure([
            'status',
            'message',
            'version',
            'timestamp',
            'endpoints',
        ]);
});

test('user can register through mobile api', function () {
    $response = $this->postJson('/api/v1/register', [
        'name' => 'Mobile User',
        'email' => 'mobile@example.com',
        'password' => 'password',
        'device_name' => 'iPhone 15 Pro',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'token',
                'token_type',
                'user' => ['id', 'name', 'email'],
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'mobile@example.com',
    ]);
});

test('user cannot register with existing email', function () {
    User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $response = $this->postJson('/api/v1/register', [
        'name' => 'Duplicate User',
        'email' => 'existing@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can login through mobile api and receive token', function () {
    User::factory()->create([
        'email' => 'login@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'login@example.com',
        'password' => 'secret123',
        'device_name' => 'Pixel 8',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'token',
                'token_type',
                'user',
            ],
        ]);
});

test('user cannot login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'invalid@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/v1/login', [
        'email' => 'invalid@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('authenticated user can retrieve their profile', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/user');

    $response->assertOk()
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.email', $user->email);
});

test('unauthenticated request to protected endpoint is rejected', function () {
    $response = $this->getJson('/api/v1/user');

    $response->assertUnauthorized();
});

test('authenticated user can logout and invalidate token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-device')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/v1/logout');

    $response->assertOk()
        ->assertJson([
            'status' => 'success',
            'message' => 'Logged out successfully.',
        ]);

    expect($user->tokens()->count())->toBe(0);
});
