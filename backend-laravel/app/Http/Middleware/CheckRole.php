<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * CheckRole Middleware
 * Usage in routes: ->middleware('role:admin') or ->middleware('role:admin,manager')
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        if ($user->is_super_admin) {
            return $next($request); // Super admin bypasses all role checks
        }

        $userRoles = \DB::table('user_roles')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('user_roles.user_id', $user->id)
            ->where('user_roles.tenant_id', $user->tenant_id)
            ->pluck('roles.slug')
            ->toArray();

        foreach ($roles as $role) {
            if (in_array($role, $userRoles)) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'You do not have the required role to access this resource.',
            'required_roles' => $roles,
        ], 403);
    }
}
