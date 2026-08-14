<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('settlements')->where('tenant_id', 1);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('merchant', 'like', "%{$request->search}%")
                  ->orWhere('settlement_id', 'like', "%{$request->search}%")
                  ->orWhere('method', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->method) {
            $query->where('method', $request->method);
        }

        $settlements = $query->orderBy('id', 'desc')->get();

        $settlements = $settlements->map(function ($s) {
            $s->amount = (float)($s->amount ?? 0);
            return $s;
        });

        return response()->json($settlements);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'merchant' => 'required|string|max:255',
            'amount'   => 'required|numeric|min:1',
            'method'   => 'nullable|string',
            'status'   => 'nullable|string',
        ]);

        $randomNum = rand(1000, 9999);
        $data = [
            'tenant_id'     => 1,
            'settlement_id' => 'SET-' . $randomNum,
            'merchant'      => $validated['merchant'],
            'date'          => now()->toDateString(),
            'amount'        => $validated['amount'],
            'method'        => $validated['method'] ?? 'Bank Transfer',
            'status'        => $validated['status'] ?? 'Pending',
            'created_at'    => now(),
            'updated_at'    => now(),
        ];

        $id = DB::table('settlements')->insertGetId($data);
        $settlement = DB::table('settlements')->where('id', $id)->first();

        return response()->json($settlement, 201);
    }

    public function show($id)
    {
        $settlement = DB::table('settlements')->where('id', $id)->where('tenant_id', 1)->first();
        if (!$settlement) {
            return response()->json(['message' => 'Settlement not found'], 404);
        }
        return response()->json($settlement);
    }

    public function update(Request $request, $id)
    {
        $data = $request->only(['merchant', 'amount', 'method', 'status']);
        $data['updated_at'] = now();

        DB::table('settlements')->where('id', $id)->where('tenant_id', 1)->update($data);
        $settlement = DB::table('settlements')->where('id', $id)->first();

        return response()->json($settlement);
    }

    public function processPayout(Request $request, $id)
    {
        DB::table('settlements')->where('id', $id)->where('tenant_id', 1)->update([
            'status'     => 'Completed',
            'updated_at' => now()
        ]);

        $settlement = DB::table('settlements')->where('id', $id)->first();
        return response()->json(['success' => true, 'settlement' => $settlement]);
    }

    public function export(Request $request)
    {
        $settlements = DB::table('settlements')->where('tenant_id', 1)->get();
        return response()->json([
            'success' => true,
            'data'    => $settlements,
            'summary' => [
                'total_amount' => $settlements->sum('amount'),
                'count'        => $settlements->count(),
            ]
        ]);
    }

    public function destroy($id)
    {
        DB::table('settlements')->where('id', $id)->where('tenant_id', 1)->delete();
        return response()->json(['success' => true, 'message' => 'Settlement deleted successfully']);
    }
}
