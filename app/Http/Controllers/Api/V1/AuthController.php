<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $deviceName = $validated['device_name'] ?? 'mobile-app';
        $token = $user->createToken($deviceName)->plainTextToken;

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? 'practitioner',
            'cabinet_name' => 'Cabinet Dentaire',
            'cabinet_room' => 'Fauteuil 1',
            'created_at' => $user->created_at?->toIso8601String(),
        ];

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

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $deviceName = $validated['device_name'] ?? 'mobile-app';
        $token = $user->createToken($deviceName)->plainTextToken;

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? 'practitioner',
            'cabinet_name' => 'Cabinet Dentaire',
            'cabinet_room' => 'Fauteuil 1',
            'created_at' => $user->created_at?->toIso8601String(),
        ];

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
        $user = $request->user();
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? 'practitioner',
            'cabinet_name' => 'Cabinet Dentaire',
            'cabinet_room' => 'Fauteuil 1',
            'created_at' => $user->created_at?->toIso8601String(),
        ];

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
