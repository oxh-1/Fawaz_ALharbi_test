<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('reviews')->where('tenant_id', 1);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('author', 'like', "%{$request->search}%")
                  ->orWhere('merchant', 'like', "%{$request->search}%")
                  ->orWhere('comment', 'like', "%{$request->search}%")
                  ->orWhere('text', 'like', "%{$request->search}%");
            });
        }

        if ($request->status && $request->status !== 'all') {
            $query->where('status', strtolower($request->status));
        }

        if ($request->rating) {
            $query->where('rating', (int)$request->rating);
        }

        $reviews = $query->orderBy('id', 'desc')->get();

        $reviews = $reviews->map(function ($r) {
            $r->rating = (int)$r->rating;
            $r->comment = $r->comment ?: ($r->text ?: '');
            $r->author = $r->author ?: 'Customer';
            $r->date = $r->date ?: ($r->created_at ? substr($r->created_at, 0, 10) : now()->toDateString());
            return $r;
        });

        return response()->json($reviews);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'author'   => 'required|string|max:255',
            'merchant' => 'required|string|max:255',
            'rating'   => 'required|integer|min:1|max:5',
            'comment'  => 'nullable|string',
            'text'     => 'nullable|string',
            'title'    => 'nullable|string',
            'status'   => 'nullable|string',
        ]);

        $text = $validated['comment'] ?? ($validated['text'] ?? '');
        $sentiment = 'positive';
        if ($validated['rating'] <= 2) {
            $sentiment = 'negative';
        } elseif ($validated['rating'] == 3) {
            $sentiment = 'neutral';
        }

        $data = [
            'tenant_id'          => 1,
            'booking_id'         => $request->booking_id ?? 1,
            'customer_id'        => 1,
            'merchant_id'        => 1,
            'service_id'         => 1,
            'author'             => $validated['author'],
            'merchant'           => $validated['merchant'],
            'rating'             => $validated['rating'],
            'title'              => $validated['title'] ?? 'Review',
            'comment'            => $text,
            'text'               => $text,
            'sentiment'          => $sentiment,
            'status'             => strtolower($validated['status'] ?? 'pending'),
            'date'               => now()->toDateString(),
            'is_helpful_count'   => 0,
            'is_not_helpful_count' => 0,
            'verified_purchase'  => 1,
            'created_at'         => now(),
            'updated_at'         => now(),
        ];

        $id = DB::table('reviews')->insertGetId($data);
        $review = DB::table('reviews')->where('id', $id)->first();

        return response()->json($review, 201);
    }

    public function show($id)
    {
        $review = DB::table('reviews')->where('id', $id)->where('tenant_id', 1)->first();
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }
        return response()->json($review);
    }

    public function moderate(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        $newStatus = strtolower($request->status);

        $updates = [
            'status'           => $newStatus,
            'moderation_notes' => $request->reject_reason ?? ($request->reason ?? null),
            'updated_at'       => now()
        ];

        DB::table('reviews')->where('id', $id)->where('tenant_id', 1)->update($updates);
        $review = DB::table('reviews')->where('id', $id)->first();

        return response()->json($review);
    }

    public function reply(Request $request, $id)
    {
        $request->validate(['response' => 'required|string']);

        DB::table('reviews')->where('id', $id)->where('tenant_id', 1)->update([
            'merchant_response'     => $request->response,
            'merchant_responded_at' => now(),
            'updated_at'            => now()
        ]);

        $review = DB::table('reviews')->where('id', $id)->first();
        return response()->json($review);
    }

    public function vote(Request $request, $id)
    {
        $helpful = $request->input('type') === 'helpful';
        $column = $helpful ? 'is_helpful_count' : 'is_not_helpful_count';

        DB::table('reviews')->where('id', $id)->increment($column);
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        DB::table('reviews')->where('id', $id)->where('tenant_id', 1)->delete();
        return response()->json(['success' => true, 'message' => 'Review deleted successfully']);
    }
}
