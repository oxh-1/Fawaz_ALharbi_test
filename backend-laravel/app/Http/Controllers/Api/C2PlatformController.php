<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class C2PlatformController extends Controller
{
    private $allowed = ['categories', 'services', 'reviews', 'contact_messages', 'ads', 'content_pages', 'settlements'];

    public function index($type, Request $request)
    {
        abort_unless(in_array($type, $this->allowed), 404);
        $query = DB::table($type)->where('tenant_id', 1);
        return response()->json($query->orderBy('id', 'desc')->get());
    }

    public function store(Request $request, $type)
    {
        abort_unless(in_array($type, $this->allowed), 404);
        $data = $request->except(['id']);
        $data['tenant_id'] = 1;
        $data['created_at'] = now();
        $data['updated_at'] = now();
        $id = DB::table($type)->insertGetId($data);
        return response()->json(DB::table($type)->where('id', $id)->first(), 201);
    }

    public function update(Request $request, $type, $id)
    {
        abort_unless(in_array($type, $this->allowed), 404);
        $data = $request->except(['id', 'tenant_id', 'created_at']);
        $data['updated_at'] = now();
        // Decode tags if it's an array because we don't have Eloquent to auto-cast for JSON
        foreach ($data as $k => $v) {
            if (is_array($v)) $data[$k] = json_encode($v);
            if (is_bool($v)) $data[$k] = $v ? 1 : 0;
        }

        DB::table($type)->where('id', $id)->where('tenant_id', 1)->update($data);
        return response()->json(DB::table($type)->where('id', $id)->first());
    }

    public function destroy($type, $id)
    {
        abort_unless(in_array($type, $this->allowed), 404);
        DB::table($type)->where('id', $id)->where('tenant_id', 1)->delete();
        return response()->json(['success' => true]);
    }

    // Settings uses a single row
    public function getSettings() {
        $settings = DB::table('c2_settings')->where('tenant_id', 1)->first();
        if (!$settings) {
            $id = DB::table('c2_settings')->insertGetId(['tenant_id'=>1, 'company_data'=>json_encode([]), 'appearance'=>json_encode([]), 'notifications'=>json_encode([]), 'security'=>json_encode([])]);
            $settings = DB::table('c2_settings')->where('id', $id)->first();
        }
        return response()->json($settings);
    }

    public function updateSettings(Request $request) {
        $data = [
            'company_data' => json_encode($request->input('company', [])),
            'appearance' => json_encode($request->input('appearance', [])),
            'notifications' => json_encode($request->input('notifications', [])),
            'security' => json_encode($request->input('security', []))
        ];
        DB::table('c2_settings')->where('tenant_id', 1)->update($data);
        return response()->json(['success' => true]);
    }
}
