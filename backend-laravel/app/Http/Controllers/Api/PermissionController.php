<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissionController extends Controller
{
    private $defaultRoles = [
        ['id' => 1, 'name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Full system access, overrides all permissions'],
        ['id' => 2, 'name' => 'Admin', 'slug' => 'admin', 'description' => 'Full tenant access, can manage all business operations'],
        ['id' => 3, 'name' => 'Manager', 'slug' => 'manager', 'description' => 'Operational management, bookings and reviews'],
        ['id' => 4, 'name' => 'Staff', 'slug' => 'staff', 'description' => 'Limited operational access, handles bookings'],
        ['id' => 5, 'name' => 'Viewer', 'slug' => 'viewer', 'description' => 'Read-only access to statistics and reports'],
    ];

    private $defaultPermissions = [
        ['id' => 1, 'name' => 'View Merchants', 'slug' => 'merchants.view', 'module' => 'Merchants', 'action' => 'view'],
        ['id' => 2, 'name' => 'Create Merchants', 'slug' => 'merchants.create', 'module' => 'Merchants', 'action' => 'create'],
        ['id' => 3, 'name' => 'Edit Merchants', 'slug' => 'merchants.edit', 'module' => 'Merchants', 'action' => 'edit'],
        ['id' => 4, 'name' => 'Delete Merchants', 'slug' => 'merchants.delete', 'module' => 'Merchants', 'action' => 'delete'],

        ['id' => 5, 'name' => 'View Bookings', 'slug' => 'bookings.view', 'module' => 'Bookings', 'action' => 'view'],
        ['id' => 6, 'name' => 'Create Bookings', 'slug' => 'bookings.create', 'module' => 'Bookings', 'action' => 'create'],
        ['id' => 7, 'name' => 'Edit Bookings', 'slug' => 'bookings.edit', 'module' => 'Bookings', 'action' => 'edit'],
        ['id' => 8, 'name' => 'Delete Bookings', 'slug' => 'bookings.delete', 'module' => 'Bookings', 'action' => 'delete'],

        ['id' => 9, 'name' => 'View Reviews', 'slug' => 'reviews.view', 'module' => 'Reviews', 'action' => 'view'],
        ['id' => 10, 'name' => 'Moderate Reviews', 'slug' => 'reviews.moderate', 'module' => 'Reviews', 'action' => 'edit'],
        ['id' => 11, 'name' => 'Delete Reviews', 'slug' => 'reviews.delete', 'module' => 'Reviews', 'action' => 'delete'],

        ['id' => 12, 'name' => 'View Settlements', 'slug' => 'settlements.view', 'module' => 'Settlements', 'action' => 'view'],
        ['id' => 13, 'name' => 'Process Settlements', 'slug' => 'settlements.process', 'module' => 'Settlements', 'action' => 'edit'],

        ['id' => 14, 'name' => 'View Ads', 'slug' => 'ads.view', 'module' => 'Ads', 'action' => 'view'],
        ['id' => 15, 'name' => 'Manage Ads', 'slug' => 'ads.manage', 'module' => 'Ads', 'action' => 'create'],

        ['id' => 16, 'name' => 'View Content', 'slug' => 'content.view', 'module' => 'Content', 'action' => 'view'],
        ['id' => 17, 'name' => 'Manage Content', 'slug' => 'content.manage', 'module' => 'Content', 'action' => 'edit'],

        ['id' => 18, 'name' => 'View Reports', 'slug' => 'reports.view', 'module' => 'Reports', 'action' => 'view'],
        ['id' => 19, 'name' => 'Export Reports', 'slug' => 'reports.export', 'module' => 'Reports', 'action' => 'create'],

        ['id' => 20, 'name' => 'Manage Permissions', 'slug' => 'permissions.manage', 'module' => 'Permissions', 'action' => 'edit'],
        ['id' => 21, 'name' => 'Manage Settings', 'slug' => 'settings.manage', 'module' => 'Settings', 'action' => 'edit'],
    ];

    public function roles()
    {
        if (Schema::hasTable('roles')) {
            $roles = DB::table('roles')->get();
            if ($roles->isNotEmpty()) {
                return response()->json($roles);
            }
        }
        return response()->json($this->defaultRoles);
    }

    public function list()
    {
        if (Schema::hasTable('permissions')) {
            $perms = DB::table('permissions')->get();
            if ($perms->isNotEmpty()) {
                return response()->json($perms);
            }
        }
        return response()->json($this->defaultPermissions);
    }

    public function rolePermissions($roleId)
    {
        // Try reading from stored settings or role_permissions table
        $setting = DB::table('c2_settings')->where('tenant_id', 1)->first();
        if ($setting && $setting->security) {
            $sec = json_decode($setting->security, true);
            if (isset($sec['role_permissions'][$roleId])) {
                return response()->json($sec['role_permissions'][$roleId]);
            }
        }

        // Default permission matrix
        if ($roleId == 1 || $roleId == 2) {
            // Super Admin / Admin has all
            return response()->json(array_column($this->defaultPermissions, 'id'));
        } elseif ($roleId == 3) {
            // Manager
            return response()->json([1, 2, 3, 5, 6, 7, 9, 10, 12, 14, 16, 18, 19]);
        } elseif ($roleId == 4) {
            // Staff
            return response()->json([1, 5, 6, 7, 9, 16, 18]);
        } else {
            // Viewer
            return response()->json([1, 5, 9, 12, 14, 16, 18]);
        }
    }

    public function syncRolePerms(Request $request, $roleId)
    {
        $permIds = $request->input('permission_ids', []);

        // Persist into c2_settings security blob
        $setting = DB::table('c2_settings')->where('tenant_id', 1)->first();
        $sec = $setting && $setting->security ? json_decode($setting->security, true) : [];
        if (!isset($sec['role_permissions'])) {
            $sec['role_permissions'] = [];
        }
        $sec['role_permissions'][$roleId] = $permIds;

        DB::table('c2_settings')->where('tenant_id', 1)->update([
            'security'   => json_encode($sec),
            'updated_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Role permissions updated successfully']);
    }
}
