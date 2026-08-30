<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Cabinet;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users/staff.
     */
    public function index(Request $request): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $query = User::query()->with('cabinet');

        if ($authUser->isSuperAdmin()) {
            // Super Admin can only see and manage Admins
            $query->whereIn('role', ['admin', 'administrator']);
        } elseif ($authUser->isAdmin()) {
            // Admin can only see staff (stock_manager, practitioner, assistant) belonging to their own cabinet
            $query->where('cabinet_id', $authUser->cabinet_id)
                ->whereIn('role', ['stock_manager', 'practitioner', 'praticien', 'assistant']);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Staff members cannot manage users.',
            ], 403);
        }

        $role = $request->query('role');
        $search = $request->query('search') ?? $request->query('query');

        $users = $query
            ->when($role, fn ($q) => $q->where('role', $role))
            ->when($search, function ($q, $search) {
                $term = '%'.mb_strtolower((string) $search).'%';
                $q->where(function ($sub) use ($term) {
                    $sub->whereRaw('LOWER(name) LIKE ?', [$term])
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
    public function show(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($authUser->isSuperAdmin()) {
            if (! $user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Super administrators can only view admin accounts.',
                ], 403);
            }
        } elseif ($authUser->isAdmin()) {
            if ($user->cabinet_id !== $authUser->cabinet_id || ! in_array($user->role, ['stock_manager', 'practitioner', 'praticien', 'assistant'], true)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You can only view staff in your own cabinet.',
                ], 403);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Staff members cannot manage users.',
            ], 403);
        }

        $userData = (new UserResource($user->loadMissing('cabinet')))->resolve();

        return response()->json([
            'status' => 'success',
            'data' => $userData,
            'user' => $userData,
        ]);
    }

    /**
     * Store a newly created user (Admin or Staff creation).
     */
    public function store(Request $request): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($authUser->isSuperAdmin()) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'role' => ['nullable', 'string', 'in:admin,administrator'],
                'password' => ['nullable', 'string', 'min:6'],
                'is_active' => ['nullable', 'boolean'],
                'cabinet_name' => ['nullable', 'string', 'max:255'],
                'cabinet_address' => ['nullable', 'string', 'max:255'],
                'cabinet_phone' => ['nullable', 'string', 'max:255'],
                'cabinet_email' => ['nullable', 'string', 'max:255'],
            ]);

            $user = DB::transaction(function () use ($validated) {
                $cabinet = Cabinet::create([
                    'name' => $validated['cabinet_name'] ?? 'Cabinet de '.$validated['name'],
                    'address' => $validated['cabinet_address'] ?? null,
                    'phone' => $validated['cabinet_phone'] ?? null,
                    'email' => $validated['cabinet_email'] ?? null,
                ]);

                $password = ! empty($validated['password'])
                    ? Hash::make($validated['password'])
                    : Hash::make('password123');

                return User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'role' => 'admin',
                    'cabinet_id' => $cabinet->id,
                    'cabinet_name' => $cabinet->name,
                    'password' => $password,
                    'is_active' => $validated['is_active'] ?? true,
                    'email_verified_at' => now(),
                ]);
            });

            $userData = (new UserResource($user->load('cabinet')))->resolve();

            return response()->json([
                'status' => 'success',
                'message' => 'Admin created and cabinet auto-provisioned successfully.',
                'data' => $userData,
                'user' => $userData,
            ], 201);
        }

        if ($authUser->isAdmin()) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'role' => ['required', 'string', 'in:stock_manager,practitioner,praticien,assistant'],
                'password' => ['nullable', 'string', 'min:6'],
                'is_active' => ['nullable', 'boolean'],
                'cabinet_room' => ['nullable', 'string', 'max:255'],
            ]);

            $password = ! empty($validated['password'])
                ? Hash::make($validated['password'])
                : Hash::make('password123');

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'cabinet_id' => $authUser->cabinet_id,
                'cabinet_name' => $authUser->cabinet?->name ?? $authUser->cabinet_name,
                'cabinet_room' => $validated['cabinet_room'] ?? 'Fauteuil 1',
                'password' => $password,
                'is_active' => $validated['is_active'] ?? true,
                'email_verified_at' => now(),
            ]);

            $userData = (new UserResource($user->load('cabinet')))->resolve();

            return response()->json([
                'status' => 'success',
                'message' => 'Staff user created successfully in your cabinet.',
                'data' => $userData,
                'user' => $userData,
            ], 201);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized. Staff members cannot create users.',
        ], 403);
    }

    /**
     * Update the specified user (Supports PUT/PATCH/POST).
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($authUser->isSuperAdmin()) {
            if (! $user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Super administrators can only modify admin accounts.',
                ], 403);
            }

            $validated = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'role' => ['sometimes', 'string', 'in:admin,administrator'],
                'is_active' => ['sometimes', 'boolean'],
                'password' => ['sometimes', 'nullable', 'string', 'min:6'],
                'cabinet_name' => ['sometimes', 'nullable', 'string', 'max:255'],
                'cabinet_address' => ['sometimes', 'nullable', 'string', 'max:255'],
                'cabinet_phone' => ['sometimes', 'nullable', 'string', 'max:255'],
                'cabinet_email' => ['sometimes', 'nullable', 'string', 'max:255'],
            ]);

            DB::transaction(function () use ($user, $validated) {
                if (isset($validated['password']) && ! empty($validated['password'])) {
                    $validated['password'] = Hash::make($validated['password']);
                } else {
                    unset($validated['password']);
                }

                if ($user->cabinet && (isset($validated['cabinet_name']) || isset($validated['cabinet_address']) || isset($validated['cabinet_phone']) || isset($validated['cabinet_email']))) {
                    $user->cabinet->update(array_filter([
                        'name' => $validated['cabinet_name'] ?? null,
                        'address' => $validated['cabinet_address'] ?? null,
                        'phone' => $validated['cabinet_phone'] ?? null,
                        'email' => $validated['cabinet_email'] ?? null,
                    ], fn ($v) => $v !== null));
                }

                $user->update($validated);
            });

            $userData = (new UserResource($user->fresh(['cabinet'])))->resolve();

            return response()->json([
                'status' => 'success',
                'message' => 'Admin updated successfully.',
                'data' => $userData,
                'user' => $userData,
            ]);
        }

        if ($authUser->isAdmin()) {
            if ($user->cabinet_id !== $authUser->cabinet_id || ! in_array($user->role, ['stock_manager', 'practitioner', 'praticien', 'assistant'], true)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You can only update staff in your own cabinet.',
                ], 403);
            }

            $validated = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'role' => ['sometimes', 'string', 'in:stock_manager,practitioner,praticien,assistant'],
                'is_active' => ['sometimes', 'boolean'],
                'password' => ['sometimes', 'nullable', 'string', 'min:6'],
                'cabinet_room' => ['sometimes', 'nullable', 'string', 'max:255'],
            ]);

            if (isset($validated['password']) && ! empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            // Ensure cabinet_id cannot be modified by admin
            unset($validated['cabinet_id']);

            $user->update($validated);

            $userData = (new UserResource($user->fresh(['cabinet'])))->resolve();

            return response()->json([
                'status' => 'success',
                'message' => 'Staff user updated successfully.',
                'data' => $userData,
                'user' => $userData,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized. Staff members cannot modify users.',
        ], 403);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($authUser->id === $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        if ($authUser->isSuperAdmin()) {
            if (! $user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Super administrators can only delete admin accounts.',
                ], 403);
            }

            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Admin deleted successfully.',
            ]);
        }

        if ($authUser->isAdmin()) {
            if ($user->cabinet_id !== $authUser->cabinet_id || ! in_array($user->role, ['stock_manager', 'practitioner', 'praticien', 'assistant'], true)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. You can only delete staff in your own cabinet.',
                ], 403);
            }

            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Staff user deleted successfully.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized. Staff members cannot delete users.',
        ], 403);
    }
}
