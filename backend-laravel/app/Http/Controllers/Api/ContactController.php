<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('contact_messages')->where('tenant_id', 1);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('subject', 'like', "%{$request->search}%")
                  ->orWhere('message', 'like', "%{$request->search}%");
            });
        }

        if ($request->has('read') && $request->read !== '') {
            $query->where('read', $request->read == '1' || $request->read === 'true' ? 1 : 0);
        }

        $messages = $query->orderBy('id', 'desc')->get();

        $messages = $messages->map(function ($m) {
            $m->read = (bool)$m->read;
            $m->date = $m->date ?: ($m->created_at ? substr($m->created_at, 0, 10) : now()->toDateString());
            return $m;
        });

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $data = [
            'tenant_id'  => 1,
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'subject'    => $validated['subject'],
            'message'    => $validated['message'],
            'date'       => now()->toDateString(),
            'read'       => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $id = DB::table('contact_messages')->insertGetId($data);
        $message = DB::table('contact_messages')->where('id', $id)->first();

        return response()->json($message, 201);
    }

    public function show($id)
    {
        $message = DB::table('contact_messages')->where('id', $id)->where('tenant_id', 1)->first();
        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }

        // Auto mark as read when viewed
        DB::table('contact_messages')->where('id', $id)->update(['read' => 1, 'updated_at' => now()]);
        $message->read = true;

        return response()->json($message);
    }

    public function markRead(Request $request, $id)
    {
        DB::table('contact_messages')->where('id', $id)->where('tenant_id', 1)->update(['read' => 1, 'updated_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function reply(Request $request, $id)
    {
        $request->validate(['reply_message' => 'required|string']);

        DB::table('contact_messages')->where('id', $id)->where('tenant_id', 1)->update([
            'read'       => 1,
            'updated_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Reply sent successfully']);
    }

    public function destroy($id)
    {
        DB::table('contact_messages')->where('id', $id)->where('tenant_id', 1)->delete();
        return response()->json(['success' => true, 'message' => 'Message deleted successfully']);
    }
}
