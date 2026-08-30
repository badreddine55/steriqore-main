<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCabinetAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user->isSuperAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Super administrators cannot access cabinet-scoped data.',
                'error' => 'super_admin_forbidden',
            ], 403);
        }

        if (! $user->cabinet_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'User is not assigned to a cabinet.',
                'error' => 'cabinet_required',
            ], 403);
        }

        return $next($request);
    }
}
