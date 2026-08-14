<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('content_pages')->where('tenant_id', 1);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('slug', 'like', "%{$request->search}%")
                  ->orWhere('content', 'like', "%{$request->search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $pages = $query->orderBy('id', 'desc')->get();
        return response()->json($pages);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'slug'    => 'nullable|string|max:255',
            'content' => 'required|string',
            'meta'    => 'nullable|string',
            'status'  => 'nullable|string',
        ]);

        $slug = $validated['slug'] ? Str::slug($validated['slug']) : Str::slug($validated['title']);

        $data = [
            'tenant_id'  => 1,
            'title'      => $validated['title'],
            'slug'       => $slug,
            'content'    => $validated['content'],
            'meta'       => $validated['meta'] ?? 'SEO Meta for ' . $validated['title'],
            'status'     => $validated['status'] ?? 'Published',
            'updated'    => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $id = DB::table('content_pages')->insertGetId($data);
        $page = DB::table('content_pages')->where('id', $id)->first();

        return response()->json($page, 201);
    }

    public function show($id)
    {
        $page = DB::table('content_pages')->where('id', $id)->where('tenant_id', 1)->first();
        if (!$page) {
            return response()->json(['message' => 'Page not found'], 404);
        }
        return response()->json($page);
    }

    public function update(Request $request, $id)
    {
        $data = $request->only(['title', 'slug', 'content', 'meta', 'status']);
        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }
        $data['updated'] = now()->toDateString();
        $data['updated_at'] = now();

        DB::table('content_pages')->where('id', $id)->where('tenant_id', 1)->update($data);
        $page = DB::table('content_pages')->where('id', $id)->first();

        return response()->json($page);
    }

    public function togglePublish($id)
    {
        $page = DB::table('content_pages')->where('id', $id)->where('tenant_id', 1)->first();
        if (!$page) {
            return response()->json(['message' => 'Page not found'], 404);
        }

        $newStatus = strtolower($page->status) === 'published' ? 'Draft' : 'Published';
        DB::table('content_pages')->where('id', $id)->update(['status' => $newStatus, 'updated' => now()->toDateString(), 'updated_at' => now()]);

        return response()->json(['success' => true, 'status' => $newStatus]);
    }

    public function destroy($id)
    {
        DB::table('content_pages')->where('id', $id)->where('tenant_id', 1)->delete();
        return response()->json(['success' => true, 'message' => 'Page deleted successfully']);
    }
}
