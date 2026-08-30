<?php

namespace Database\Factories;

use App\Models\Cabinet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'practitioner',
            'is_active' => true,
            'cabinet_id' => fn (array $attributes) => in_array($attributes['role'] ?? 'practitioner', ['super_admin', 'superadmin'], true)
                ? null
                : (Cabinet::first()?->id ?? Cabinet::factory()->create()->id),
            'cabinet_name' => 'Cabinet Dentaire',
            'cabinet_room' => 'Fauteuil 1',
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the user is a super admin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'super_admin',
            'cabinet_id' => null,
            'cabinet_name' => null,
            'cabinet_room' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(?Cabinet $cabinet = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'cabinet_id' => $cabinet?->id ?? (Cabinet::first()?->id ?? Cabinet::factory()->create()->id),
            'cabinet_name' => $cabinet?->name ?? 'Cabinet Dentaire',
        ]);
    }

    /**
     * Indicate that the user is a stock manager.
     */
    public function stockManager(?Cabinet $cabinet = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'stock_manager',
            'cabinet_id' => $cabinet?->id ?? (Cabinet::first()?->id ?? Cabinet::factory()->create()->id),
            'cabinet_name' => $cabinet?->name ?? 'Cabinet Dentaire',
        ]);
    }

    /**
     * Indicate that the user is a practitioner.
     */
    public function practitioner(?Cabinet $cabinet = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'practitioner',
            'cabinet_id' => $cabinet?->id ?? (Cabinet::first()?->id ?? Cabinet::factory()->create()->id),
            'cabinet_name' => $cabinet?->name ?? 'Cabinet Dentaire',
        ]);
    }

    /**
     * Link the user to a specific cabinet.
     */
    public function forCabinet(Cabinet $cabinet): static
    {
        return $this->state(fn (array $attributes) => [
            'cabinet_id' => $cabinet->id,
            'cabinet_name' => $cabinet->name,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }
}
