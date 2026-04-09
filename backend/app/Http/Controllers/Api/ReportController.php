<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Merchant;
use App\Models\Review;
use App\Models\Settlement;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // GET /api/reports/summary
    public function summary(Request $request)
    {
        $tenantId  = $request->user()->tenant_id;
        $dateFrom  = $request->from ?? now()->startOfMonth()->toDateString();
        $dateTo    = $request->to   ?? now()->toDateString();

        $revenue     = Settlement::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->sum('amount');

        $totalFees   = Settlement::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->sum('fee');

        $bookings    = Booking::where('tenant_id', $tenantId)
            ->whereBetween('booking_date', [$dateFrom, $dateTo])
            ->count();

        $avgRating   = Review::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->avg('rating');

        $cancelled   = Booking::where('tenant_id', $tenantId)
            ->whereBetween('booking_date', [$dateFrom, $dateTo])
            ->where('status', 'cancelled')
            ->count();

        $cancelRate  = $bookings > 0 ? round(($cancelled / $bookings) * 100, 1) : 0;

        $newMerchants = Merchant::where('tenant_id', $tenantId)
            ->whereBetween('joined_date', [$dateFrom, $dateTo])
            ->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'total_revenue'    => (float)$revenue,
                'total_fees'       => (float)$totalFees,
                'total_bookings'   => $bookings,
                'avg_rating'       => round((float)$avgRating, 1),
                'cancellation_rate'=> $cancelRate,
                'new_merchants'    => $newMerchants,
                'active_merchants' => Merchant::where('tenant_id', $tenantId)->where('status', 'active')->count(),
                'period'           => ['from' => $dateFrom, 'to' => $dateTo],
            ],
        ]);
    }

    // GET /api/reports/revenue
    public function revenue(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // Monthly revenue for the last 12 months
        $monthly = DB::table('settlements')
            ->select(
                DB::raw('YEAR(paid_at) as year'),
                DB::raw('MONTH(paid_at) as month'),
                DB::raw('MONTHNAME(paid_at) as month_name'),
                DB::raw('SUM(amount) as total')
            )
            ->where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereNotNull('paid_at')
            ->whereDate('paid_at', '>=', now()->subMonths(12)->startOfMonth())
            ->groupBy('year', 'month', 'month_name')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Revenue by merchant (top 10)
        $byMerchant = DB::table('settlements')
            ->join('merchants', 'settlements.merchant_id', '=', 'merchants.id')
            ->select(
                'merchants.name',
                DB::raw('SUM(settlements.amount) as total')
            )
            ->where('settlements.tenant_id', $tenantId)
            ->where('settlements.status', 'paid')
            ->groupBy('merchants.id', 'merchants.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => compact('monthly', 'byMerchant'),
        ]);
    }

    // GET /api/reports/bookings
    public function bookingStats(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $byStatus = Booking::where('tenant_id', $tenantId)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $byCategory = DB::table('bookings')
            ->join('services', 'bookings.service_id', '=', 'services.id')
            ->join('categories', 'services.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('COUNT(*) as count'))
            ->where('bookings.tenant_id', $tenantId)
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => compact('byStatus', 'byCategory'),
        ]);
    }

    // GET /api/reports/merchants
    public function merchantStats(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        $stats = DB::table('merchants')
            ->leftJoin('bookings', function ($join) {
                $join->on('bookings.merchant_id', '=', 'merchants.id')
                     ->whereNotIn('bookings.status', ['cancelled']);
            })
            ->leftJoin('reviews', function ($join) {
                $join->on('reviews.merchant_id', '=', 'merchants.id')
                     ->where('reviews.status', 'approved');
            })
            ->leftJoin('settlements', function ($join) {
                $join->on('settlements.merchant_id', '=', 'merchants.id')
                     ->where('settlements.status', 'paid');
            })
            ->select(
                'merchants.id',
                'merchants.name',
                'merchants.status',
                DB::raw('COUNT(DISTINCT bookings.id) as bookings_count'),
                DB::raw('COALESCE(SUM(DISTINCT settlements.amount), 0) as revenue'),
                DB::raw('COALESCE(AVG(reviews.rating), 0) as avg_rating')
            )
            ->where('merchants.tenant_id', $tenantId)
            ->groupBy('merchants.id', 'merchants.name', 'merchants.status')
            ->orderByDesc('revenue')
            ->get();

        return response()->json(['success' => true, 'data' => $stats]);
    }

    // POST /api/reports/export
    public function export(Request $request)
    {
        // In a real app, this would generate CSV/PDF. Here we return JSON for download.
        $summary = $this->summary($request)->getData(true);
        return response()->json([
            'success'    => true,
            'export_url' => '/api/reports/download?token=' . $request->bearerToken(),
            'data'       => $summary['data'],
        ]);
    }
}
