<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('services')->where('tenant_id', 1);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('merchant', 'like', "%{$request->search}%")
                  ->orWhere('category', 'like', "%{$request->search}%");
            });
        }

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->merchant) {
            $query->where('merchant', $request->merchant);
        }

        if ($request->has('active') && $request->active !== '' && $request->active !== null) {
            $query->where('active', $request->active == '1' || $request->active === 'true' ? 1 : 0);
        }

        $services = $query->orderBy('id', 'desc')->get();

        $services = $services->map(function ($s) {
            if (is_string($s->tags)) {
                $decoded = json_decode($s->tags, true);
                $s->tags = is_array($decoded) ? $decoded : ($s->tags ? explode(',', $s->tags) : []);
            }
            $s->active = (bool)$s->active;
            return $s;
        });

        return response()->json($services);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'merchant' => 'required|string|max:255',
            'tags'     => 'nullable',
            'active'   => 'nullable|boolean',
        ]);

        $tags = $request->input('tags');
        if (is_array($tags)) {
            $tags = json_encode($tags);
        }

        $data = [
            'tenant_id'  => 1,
            'name'       => $validated['name'],
            'category'   => $validated['category'],
            'merchant'   => $validated['merchant'],
            'tags'       => $tags ?? json_encode([]),
            'active'     => $request->has('active') ? ($request->active ? 1 : 0) : 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $id = DB::table('services')->insertGetId($data);
        $service = DB::table('services')->where('id', $id)->first();

        return response()->json($service, 201);
    }

    public function show($id)
    {
        $service = DB::table('services')->where('id', $id)->where('tenant_id', 1)->first();
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }
        return response()->json($service);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:255',
            'merchant' => 'sometimes|string|max:255',
            'tags'     => 'nullable',
            'active'   => 'nullable|boolean',
        ]);

        $data = $request->only(['name', 'category', 'merchant', 'active']);
        if ($request->has('tags')) {
            $tags = $request->input('tags');
            $data['tags'] = is_array($tags) ? json_encode($tags) : $tags;
        }
        if ($request->has('active')) {
            $data['active'] = $request->active ? 1 : 0;
        }
        $data['updated_at'] = now();

        DB::table('services')->where('id', $id)->where('tenant_id', 1)->update($data);
        $service = DB::table('services')->where('id', $id)->first();

        return response()->json($service);
    }

    public function toggle($id)
    {
        $service = DB::table('services')->where('id', $id)->where('tenant_id', 1)->first();
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $newActive = $service->active ? 0 : 1;
        DB::table('services')->where('id', $id)->update(['active' => $newActive, 'updated_at' => now()]);

        return response()->json(['success' => true, 'active' => (bool)$newActive]);
    }

    public function destroy($id)
    {
        DB::table('services')->where('id', $id)->where('tenant_id', 1)->delete();
        return response()->json(['success' => true, 'message' => 'Service deleted successfully']);
    }
}
