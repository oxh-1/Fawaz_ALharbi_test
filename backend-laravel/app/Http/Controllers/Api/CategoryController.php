<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('categories')->where('tenant_id', 1);

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $categories = $query->orderBy('id', 'asc')->get();

        // Calculate service counts dynamically
        $servicesCounts = DB::table('services')
            ->where('tenant_id', 1)
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category');

        $categories = $categories->map(function ($cat) use ($servicesCounts) {
            $cat->service_count = $servicesCounts[$cat->name] ?? (is_numeric($cat->services) ? (int)$cat->services : 0);
            return $cat;
        });

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'icon'     => 'nullable|string|max:50',
            'services' => 'nullable',
            'status'   => 'nullable|string',
        ]);

        $data = [
            'tenant_id'  => 1,
            'name'       => $validated['name'],
            'icon'       => $validated['icon'] ?? '📦',
            'services'   => is_array($validated['services'] ?? null) ? json_encode($validated['services']) : ($validated['services'] ?? '0'),
            'status'     => strtolower($validated['status'] ?? 'active'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $id = DB::table('categories')->insertGetId($data);
        $category = DB::table('categories')->where('id', $id)->first();

        return response()->json($category, 201);
    }

    public function show($id)
    {
        $category = DB::table('categories')->where('id', $id)->where('tenant_id', 1)->first();
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'icon'     => 'nullable|string|max:50',
            'services' => 'nullable',
            'status'   => 'nullable|string',
        ]);

        $data = $request->only(['name', 'icon', 'services', 'status']);
        if (isset($data['status'])) {
            $data['status'] = strtolower($data['status']);
        }
        if (isset($data['services']) && is_array($data['services'])) {
            $data['services'] = json_encode($data['services']);
        }
        $data['updated_at'] = now();

        DB::table('categories')->where('id', $id)->where('tenant_id', 1)->update($data);
        $category = DB::table('categories')->where('id', $id)->first();

        return response()->json($category);
    }

    public function destroy($id)
    {
        DB::table('categories')->where('id', $id)->where('tenant_id', 1)->delete();
        return response()->json(['success' => true, 'message' => 'Category deleted successfully']);
    }

    public function toggle($id)
    {
        $cat = DB::table('categories')->where('id', $id)->where('tenant_id', 1)->first();
        if (!$cat) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $newStatus = strtolower($cat->status) === 'active' ? 'inactive' : 'active';
        DB::table('categories')->where('id', $id)->update(['status' => $newStatus, 'updated_at' => now()]);

        return response()->json(['success' => true, 'status' => $newStatus]);
    }

    public function reorder(Request $request)
    {
        return response()->json(['success' => true]);
    }
}
