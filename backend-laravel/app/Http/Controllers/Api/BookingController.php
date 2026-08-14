<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('bookings')->where('tenant_id', 1);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('client', 'like', "%{$request->search}%")
                  ->orWhere('merchant', 'like', "%{$request->search}%")
                  ->orWhere('service', 'like', "%{$request->search}%");
            });
        }

        if ($request->status && $request->status !== 'all' && $request->status !== 'All') {
            $query->where('status', strtolower($request->status));
        }

        if ($request->date) {
            $query->whereDate('scheduled_at', $request->date);
        }

        if ($request->merchant) {
            $query->where('merchant', $request->merchant);
        }

        $bookings = $query->orderBy('scheduled_at', 'desc')->orderBy('id', 'desc')->get();

        $bookings = $bookings->map(function ($b) {
            if ($b->scheduled_at) {
                $dt = Carbon::parse($b->scheduled_at);
                $b->date = $dt->format('Y-m-d');
                $b->time = $dt->format('H:i');
                $b->day = (int)$dt->format('d');
                $b->month = (int)$dt->format('m');
                $b->year = (int)$dt->format('Y');
            } else {
                $b->date = now()->toDateString();
                $b->time = '10:00';
            }
            $b->total_price = (float)($b->total_price ?? 0);
            return $b;
        });

        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client'           => 'required|string|max:255',
            'merchant'         => 'nullable|string|max:255',
            'service'          => 'required|string|max:255',
            'scheduled_at'     => 'nullable|date',
            'date'             => 'nullable|string',
            'time'             => 'nullable|string',
            'duration_minutes' => 'nullable|integer',
            'total_price'      => 'nullable|numeric',
            'status'           => 'nullable|string',
            'payment_status'   => 'nullable|string',
            'payment_method'   => 'nullable|string',
            'customer_notes'   => 'nullable|string',
        ]);

        $scheduledAt = $request->scheduled_at;
        if (!$scheduledAt && $request->date && $request->time) {
            $scheduledAt = Carbon::parse($request->date . ' ' . $request->time);
        } elseif (!$scheduledAt) {
            $scheduledAt = now();
        }

        $data = [
            'tenant_id'        => 1,
            'customer_id'      => 1,
            'merchant_id'      => 1,
            'service_id'       => 1,
            'client'           => $validated['client'],
            'merchant'         => $validated['merchant'] ?? 'Company 2 Partner',
            'service'          => $validated['service'],
            'scheduled_at'     => Carbon::parse($scheduledAt),
            'duration_minutes' => $validated['duration_minutes'] ?? 60,
            'base_price'       => $validated['total_price'] ?? 100,
            'discount_amount'  => 0,
            'tax_amount'       => ($validated['total_price'] ?? 100) * 0.15,
            'total_price'      => $validated['total_price'] ?? 115,
            'payment_status'   => $validated['payment_status'] ?? 'paid',
            'payment_method'   => $validated['payment_method'] ?? 'card',
            'status'           => strtolower($validated['status'] ?? 'pending'),
            'customer_notes'   => $validated['customer_notes'] ?? null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ];

        $id = DB::table('bookings')->insertGetId($data);
        $booking = DB::table('bookings')->where('id', $id)->first();

        return response()->json($booking, 201);
    }

    public function show($id)
    {
        $booking = DB::table('bookings')->where('id', $id)->where('tenant_id', 1)->first();
        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }
        return response()->json($booking);
    }

    public function update(Request $request, $id)
    {
        $data = $request->only([
            'client', 'merchant', 'service', 'scheduled_at', 'duration_minutes',
            'total_price', 'status', 'payment_status', 'payment_method', 'customer_notes'
        ]);

        if (isset($data['status'])) {
            $data['status'] = strtolower($data['status']);
        }
        if (isset($data['scheduled_at'])) {
            $data['scheduled_at'] = Carbon::parse($data['scheduled_at']);
        }

        $data['updated_at'] = now();

        DB::table('bookings')->where('id', $id)->where('tenant_id', 1)->update($data);
        $booking = DB::table('bookings')->where('id', $id)->first();

        return response()->json($booking);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        $newStatus = strtolower($request->status);

        $updates = ['status' => $newStatus, 'updated_at' => now()];
        if ($newStatus === 'cancelled') {
            $updates['cancelled_at'] = now();
            $updates['cancellation_reason'] = $request->reason ?? 'Cancelled by Admin';
        }

        DB::table('bookings')->where('id', $id)->where('tenant_id', 1)->update($updates);
        $booking = DB::table('bookings')->where('id', $id)->first();

        return response()->json($booking);
    }

    public function reschedule(Request $request, $id)
    {
        $request->validate(['new_scheduled_at' => 'required|date']);
        $newTime = Carbon::parse($request->new_scheduled_at);

        DB::table('bookings')->where('id', $id)->where('tenant_id', 1)->update([
            'scheduled_at'   => $newTime,
            'rescheduled_at' => now(),
            'updated_at'     => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Booking rescheduled successfully']);
    }

    public function cancel(Request $request, $id)
    {
        DB::table('bookings')->where('id', $id)->where('tenant_id', 1)->update([
            'status'              => 'cancelled',
            'cancellation_reason' => $request->reason ?? 'Cancelled by customer',
            'cancelled_by'        => 'customer',
            'cancelled_at'        => now(),
            'updated_at'          => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Booking cancelled successfully']);
    }

    public function calendar($year = null, $month = null)
    {
        $year = $year ?: now()->year;
        $month = $month ?: now()->month;

        $bookings = DB::table('bookings')
            ->where('tenant_id', 1)
            ->whereYear('scheduled_at', $year)
            ->whereMonth('scheduled_at', $month)
            ->get();

        $grouped = [];
        foreach ($bookings as $b) {
            $day = Carbon::parse($b->scheduled_at)->format('Y-m-d');
            if (!isset($grouped[$day])) {
                $grouped[$day] = [];
            }
            $grouped[$day][] = $b;
        }

        return response()->json($grouped);
    }

    public function destroy($id)
    {
        DB::table('bookings')->where('id', $id)->where('tenant_id', 1)->delete();
        return response()->json(['success' => true, 'message' => 'Booking deleted successfully']);
    }
}
