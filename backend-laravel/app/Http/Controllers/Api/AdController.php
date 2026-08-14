<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('ads')->where('tenant_id', 1);

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $ads = $query->orderBy('id', 'desc')->get();
        return response()->json($ads);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'type'   => 'required|string',
            'start'  => 'required|string',
            'end'    => 'required|string',
            'status' => 'nullable|string',
        ]);

        $data = [
            'tenant_id'   => 1,
            'name'        => $validated['name'],
            'type'        => $validated['type'],
            'start'       => $validated['start'],
            'end'         => $validated['end'],
            'impressions' => 0,
            'clicks'      => 0,
            'status'      => $validated['status'] ?? 'Active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ];

        $id = DB::table('ads')->insertGetId($data);
        $ad = DB::table('ads')->where('id', $id)->first();

        return response()->json($ad, 201);
    }

    public function show($id)
    {
        $ad = DB::table('ads')->where('id', $id)->where('tenant_id', 1)->first();
        if (!$ad) {
            return response()->json(['message' => 'Ad not found'], 404);
        }
        return response()->json($ad);
    }

    public function update(Request $request, $id)
    {
        $data = $request->only(['name', 'type', 'start', 'end', 'status', 'impressions', 'clicks']);
        $data['updated_at'] = now();

        DB::table('ads')->where('id', $id)->where('tenant_id', 1)->update($data);
        $ad = DB::table('ads')->where('id', $id)->first();

        return response()->json($ad);
    }

    public function toggle($id)
    {
        $ad = DB::table('ads')->where('id', $id)->where('tenant_id', 1)->first();
        if (!$ad) {
            return response()->json(['message' => 'Ad not found'], 404);
        }

        $newStatus = strtolower($ad->status) === 'active' ? 'Inactive' : 'Active';
        DB::table('ads')->where('id', $id)->update(['status' => $newStatus, 'updated_at' => now()]);

        return response()->json(['success' => true, 'status' => $newStatus]);
    }

    public function trackClick($id)
    {
        DB::table('ads')->where('id', $id)->increment('clicks');
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        DB::table('ads')->where('id', $id)->where('tenant_id', 1)->delete();
        return response()->json(['success' => true, 'message' => 'Ad deleted successfully']);
    }
}
