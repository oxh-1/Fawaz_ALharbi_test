<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * CheckPermission Middleware
 * Usage: ->middleware('permission:merchants.create')
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        if ($user->is_super_admin) {
            return $next($request);
        }

        $hasPermission = \DB::table('user_roles')
            ->join('role_permissions', 'user_roles.role_id', '=', 'role_permissions.role_id')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('user_roles.user_id', $user->id)
            ->where('user_roles.tenant_id', $user->tenant_id)
            ->where('permissions.slug', $permission)
            ->exists();

        if (!$hasPermission) {
            return response()->json([
                'success'    => false,
                'message'    => "Permission denied: '{$permission}' is required.",
                'permission' => $permission,
            ], 403);
        }

        return $next($request);
    }
}
