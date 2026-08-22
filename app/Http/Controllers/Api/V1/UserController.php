<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users/staff.
     */
    public function index(Request $request): JsonResponse
    {
        $role = $request->query('role');
        $search = $request->query('search') ?? $request->query('query');

        $users = User::query()
            ->when($role, fn ($q) => $q->where('role', $role))
            ->when($search, function ($query, $search) {
                $term = '%'.mb_strtolower((string) $search).'%';
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
                });
            })
            ->orderBy('id')
            ->get();

        $userData = UserResource::collection($users)->resolve();

        return response()->json([
            'status' => 'success',
            'data' => $userData,
            'users' => $userData,
            'total' => count($userData),
            'count' => count($userData),
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $userData = (new UserResource($user))->resolve();

        return response()->json([
            'status' => 'success',
            'data' => $userData,
            'user' => $userData,
        ]);
    }

    /**
     * Store a newly created user (Admin staff creation).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role' => ['nullable', 'string', 'in:admin,administrator,practitioner,assistant'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['nullable', 'boolean'],
            'cabinet_name' => ['nullable', 'string', 'max:255'],
            'cabinet_room' => ['nullable', 'string', 'max:255'],
        ]);

        $password = ! empty($validated['password'])
            ? Hash::make($validated['password'])
            : Hash::make('password123');

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'] ?? 'practitioner',
            'password' => $password,
            'is_active' => $validated['is_active'] ?? true,
            'cabinet_name' => $validated['cabinet_name'] ?? 'Cabinet Dentaire',
            'cabinet_room' => $validated['cabinet_room'] ?? 'Fauteuil 1',
            'email_verified_at' => now(),
        ]);

        $userData = (new UserResource($user))->resolve();

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully.',
            'data' => $userData,
            'user' => $userData,
        ], 201);
    }

    /**
     * Update the specified user (Supports PUT/PATCH/POST).
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['sometimes', 'string', 'in:admin,administrator,practitioner,assistant'],
            'is_active' => ['sometimes', 'boolean'],
            'password' => ['sometimes', 'nullable', 'string', 'min:6'],
            'cabinet_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cabinet_room' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        if (isset($validated['password']) && ! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        $userData = (new UserResource($user->fresh()))->resolve();

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully.',
            'data' => $userData,
            'user' => $userData,
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()?->id === $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully.',
        ]);
    }
}
