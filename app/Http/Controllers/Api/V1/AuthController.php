<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Cabinet;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user and return an API token.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', Password::defaults()],
            'role' => ['nullable', 'string', 'in:super_admin,admin,administrator,stock_manager,practitioner,praticien,assistant'],
            'cabinet_name' => ['nullable', 'string', 'max:255'],
            'cabinet_room' => ['nullable', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $role = $validated['role'] ?? 'practitioner';
        $cabinetId = null;

        if ($role !== 'super_admin') {
            if (in_array($role, ['admin', 'administrator'], true)) {
                $cabinet = Cabinet::create([
                    'name' => $validated['cabinet_name'] ?? 'Cabinet de '.$validated['name'],
                ]);
                $cabinetId = $cabinet->id;
            } else {
                $cabinet = Cabinet::first() ?? Cabinet::create([
                    'name' => $validated['cabinet_name'] ?? 'Cabinet Dentaire',
                ]);
                $cabinetId = $cabinet->id;
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'cabinet_id' => $cabinetId,
            'cabinet_name' => $validated['cabinet_name'] ?? ($cabinetId ? 'Cabinet Dentaire' : null),
            'cabinet_room' => $validated['cabinet_room'] ?? ($cabinetId ? 'Fauteuil 1' : null),
            'email_verified_at' => now(),
        ]);

        $deviceName = $validated['device_name'] ?? 'mobile-app';
        $token = $user->createToken($deviceName)->plainTextToken;

        $userData = (new UserResource($user))->resolve();

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully.',
            'token' => $token,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $userData,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $userData,
                ...$userData,
            ],
        ], 201);
    }

    /**
     * Authenticate user credentials and return an API token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::with('cabinet')->where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $deviceName = $validated['device_name'] ?? 'mobile-app';
        $token = $user->createToken($deviceName)->plainTextToken;

        $userData = (new UserResource($user))->resolve();

        return response()->json([
            'status' => 'success',
            'message' => 'Authenticated successfully.',
            'token' => $token,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $userData,
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => $userData,
                ...$userData,
            ],
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('cabinet');
        $userData = (new UserResource($user))->resolve();

        return response()->json([
            'status' => 'success',
            'user' => $userData,
            'data' => [
                'user' => $userData,
                ...$userData,
            ],
        ]);
    }

    /**
     * Revoke the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully.',
        ]);
    }
}
