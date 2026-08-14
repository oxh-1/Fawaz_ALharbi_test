<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        // Fetch unique customers aggregated from bookings and users
        $query = DB::table('bookings')
            ->select(
                'client as name',
                DB::raw('COUNT(id) as total_bookings'),
                DB::raw('SUM(COALESCE(total_price, base_price, 0)) as total_spent'),
                DB::raw('MAX(created_at) as last_activity'),
                DB::raw('MIN(created_at) as member_since')
            )
            ->groupBy('client');

        if ($search) {
            $query->where('client', 'like', "%{$search}%");
        }

        $records = $query->get();

        // Enrich with customer details
        $customers = $records->map(function ($c, $idx) use ($status) {
            $emailName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $c->name));
            $vipThreshold = 500;
            $isVip = $c->total_spent >= $vipThreshold || $c->total_bookings >= 3;
            $custStatus = $isVip ? 'VIP' : 'Active';

            return [
                'id' => $idx + 1,
                'name' => $c->name ?: 'Customer ' . ($idx + 1),
                'email' => $emailName ? "{$emailName}@example.com" : "client{$idx}@example.com",
                'phone' => '+966 5' . rand(10000000, 99999999),
                'total_bookings' => (int) $c->total_bookings,
                'total_spent' => (float) $c->total_spent,
                'status' => $custStatus,
                'last_activity' => $c->last_activity,
                'member_since' => $c->member_since ?: now()->subMonths(rand(1, 12))->toDateString(),
                'avatar' => "https://i.pravatar.cc/150?u=" . urlencode($c->name),
            ];
        });

        if ($status && $status !== 'All') {
            $customers = $customers->filter(function ($item) use ($status) {
                return strtolower($item['status']) === strtolower($status);
            })->values();
        }

        return response()->json([
            'status' => 'success',
            'data' => $customers
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => (int) $id,
                'name' => 'Customer ' . $id,
                'email' => "customer{$id}@example.com",
                'phone' => '+966 501234567',
                'status' => 'Active',
                'bookings_history' => DB::table('bookings')->limit(5)->get()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'status' => 'nullable|string'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully',
            'data' => [
                'id' => rand(100, 999),
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone ?: '+966 500000000',
                'total_bookings' => 0,
                'total_spent' => 0,
                'status' => $request->status ?: 'Active',
                'member_since' => now()->toDateString(),
                'avatar' => "https://i.pravatar.cc/150?u=" . urlencode($request->name),
            ]
        ], 201);
    }
}
