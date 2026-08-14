<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Merchant;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Settlement;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // ── GET /api/admin/users ─────────────────────────────────────────────────
    public function users(Request $request)
    {
        $users = User::with('roles')
            ->when($request->search, fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            )
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $users]);
    }

    // ── GET /api/admin/users/{user} ──────────────────────────────────────────
    public function showUser(User $user)
    {
        return response()->json([
            'success' => true,
            'data'    => $user->load('roles', 'tenant'),
        ]);
    }

    // ── PUT /api/admin/users/{user} ──────────────────────────────────────────
    public function updateUser(Request $request, User $user)
    {
        $old = $user->toArray();

        $request->validate([
            'name'           => 'sometimes|string|max:255',
            'email'          => 'sometimes|email|unique:users,email,' . $user->id,
            'status'         => 'sometimes|in:active,inactive,banned',
            'is_super_admin' => 'sometimes|boolean',
            'role_ids'       => 'sometimes|array',
            'role_ids.*'     => 'exists:roles,id',
        ]);

        $user->update($request->only('name', 'email', 'status', 'is_super_admin'));

        if ($request->has('role_ids')) {
            // Sync roles (delete old, insert new)
            \DB::table('user_roles')->where('user_id', $user->id)->delete();
            foreach ($request->role_ids as $roleId) {
                \DB::table('user_roles')->insert([
                    'user_id'    => $user->id,
                    'role_id'    => $roleId,
                    'tenant_id'  => $user->tenant_id ?? 1,
                    'created_at' => now(),
                ]);
            }
        }

        AuditLog::create([
            'user_id'   => $request->user()->id,
            'action'    => 'admin.update_user',
            'model'     => 'User',
            'model_id'  => $user->id,
            'old_data'  => $old,
            'new_data'  => $user->fresh()->toArray(),
            'ip_address'=> $request->ip(),
        ]);

        return response()->json(['success' => true, 'data' => $user->fresh()->load('roles')]);
    }

    // ── PATCH /api/admin/users/{user}/status ─────────────────────────────────
    public function toggleUserStatus(Request $request, User $user)
    {
        if ($user->is_super_admin && $request->status === 'banned') {
            return response()->json(['success' => false, 'message' => 'Cannot ban a super admin.'], 403);
        }

        $request->validate(['status' => 'required|in:active,inactive,banned']);
        $user->update(['status' => $request->status]);

        return response()->json(['success' => true, 'data' => $user]);
    }

    // ── DELETE /api/admin/users/{user} ───────────────────────────────────────
    public function deleteUser(Request $request, User $user)
    {
        if ($user->is_super_admin) {
            return response()->json(['success' => false, 'message' => 'Cannot delete super admin.'], 403);
        }

        AuditLog::create([
            'user_id'   => $request->user()->id,
            'action'    => 'admin.delete_user',
            'model'     => 'User',
            'model_id'  => $user->id,
            'old_data'  => $user->toArray(),
            'ip_address'=> $request->ip(),
        ]);

        $user->delete();

        return response()->json(['success' => true, 'message' => 'User deleted.']);
    }

    // ── GET /api/admin/audit-logs ────────────────────────────────────────────
    public function auditLogs(Request $request)
    {
        $logs = AuditLog::with('user:id,name,email')
            ->when($request->action, fn($q) => $q->where('action', $request->action))
            ->when($request->model,  fn($q) => $q->where('model', $request->model))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->orderByDesc('created_at')
            ->paginate(50);

        return response()->json(['success' => true, 'data' => $logs]);
    }

    // ── GET /api/admin/stats ─────────────────────────────────────────────────
    public function globalStats()
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'total_users'       => User::count(),
                'total_merchants'   => Merchant::count(),
                'total_bookings'    => Booking::count(),
                'pending_reviews'   => Review::where('status', 'pending')->count(),
                'total_settlements' => Settlement::sum('amount'),
                'active_merchants'  => Merchant::where('status', 'active')->count(),
                'today_bookings'    => Booking::whereDate('booking_date', today())->count(),
            ],
        ]);
    }

    // ── GET /api/admin/tenants ───────────────────────────────────────────────
    public function tenants()
    {
        $tenants = \App\Models\Tenant::withCount(['merchants', 'bookings'])->get();
        return response()->json(['success' => true, 'data' => $tenants]);
    }

    // ── PUT /api/admin/merchants/{merchant}/force ─────────────────────────────
    public function forceMerchantUpdate(Request $request, Merchant $merchant)
    {
        $merchant->update($request->all());
        return response()->json(['success' => true, 'data' => $merchant->fresh()]);
    }

    // ── PUT /api/admin/bookings/{booking}/force ───────────────────────────────
    public function forceBookingUpdate(Request $request, Booking $booking)
    {
        $booking->update($request->all());
        return response()->json(['success' => true, 'data' => $booking->fresh()]);
    }
}
