<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerBookingController extends Controller
{
    public function reschedule(Request $request, $id)
    {
        $request->validate(['new_scheduled_at' => 'required|date']);
        
        DB::table('bookings')->where('id', $id)->update([
            'scheduled_at' => Carbon::parse($request->new_scheduled_at)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Booking rescheduled successfully'
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $request->validate(['cancellation_reason' => 'required|string']);

        DB::table('bookings')->where('id', $id)->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
            'refund_amount' => DB::raw('total_price'),
            'cancelled_by' => 'customer',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Booking cancelled'
        ]);
    }

    public function createReview(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        DB::table('reviews')->insert([
            'booking_id' => $id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'created_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Review submitted successfully'
        ]);
    }

    public function favoriteMerchant(Request $request, $merchantId)
    {
        // Mock authentication usage
        DB::table('customer_favorites')->updateOrInsert(
            ['customer_id' => 1, 'merchant_id' => $merchantId],
            ['created_at' => now()]
        );

        return response()->json(['status' => 'success']);
    }

    public function availableSlots(Request $request, $serviceId)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'available_slots' => [
                    ['date' => now()->toDateString(), 'slots' => [['time' => '10:00', 'available' => true], ['time' => '14:00', 'available' => true]]]
                ]
            ]
        ]);
    }
}
