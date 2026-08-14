<?php
// backend-laravel/routes/api.php
// Full-Featured Enterprise API Route Registry

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MerchantController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CustomerBookingController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\AdController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\SettlementController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\C2PlatformController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\RealEstateController;
use App\Http\Controllers\Api\CourseController;

// ─────────────────────────────────────────
// Public Routes (No Auth Required)
// ─────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('google',   [AuthController::class, 'googleLogin']);
});

// Domain validation
Route::get('domain/check', [DomainController::class, 'check']);

// Public read-only / guest inquiries
Route::post('contact', [ContactController::class, 'store']);

// ─────────────────────────────────────────
// Authenticated Routes (Sanctum Token)
// ─────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth & Profile
    Route::get('auth/me',              [AuthController::class, 'me']);
    Route::post('auth/logout',         [AuthController::class, 'logout']);
    Route::put('auth/profile',         [SettingController::class, 'updateCompany']);
    Route::post('auth/change-password',[SettingController::class, 'updatePassword']);

    // Merchants
    Route::apiResource('merchants', MerchantController::class);
    Route::patch('merchants/{merchant}/status', [MerchantController::class, 'updateStatus']);

    // Categories
    Route::apiResource('categories', CategoryController::class);
    Route::patch('categories/{id}/toggle', [CategoryController::class, 'toggle']);
    Route::post('categories/reorder', [CategoryController::class, 'reorder']);

    // Services
    Route::apiResource('services', ServiceController::class);
    Route::patch('services/{id}/toggle', [ServiceController::class, 'toggle']);

    // Bookings
    Route::get('bookings', [BookingController::class, 'index']);
    Route::post('bookings', [BookingController::class, 'store']);
    Route::get('bookings/calendar/{year?}/{month?}', [BookingController::class, 'calendar']);
    Route::get('bookings/{id}', [BookingController::class, 'show']);
    Route::put('bookings/{id}', [BookingController::class, 'update']);
    Route::patch('bookings/{id}/status', [BookingController::class, 'updateStatus']);
    Route::patch('bookings/{id}/reschedule', [BookingController::class, 'reschedule']);
    Route::patch('bookings/{id}/cancel', [BookingController::class, 'cancel']);
    Route::delete('bookings/{id}', [BookingController::class, 'destroy']);

    // Customer Portal Booking Utilities
    Route::patch('c2/bookings/{id}/reschedule', [CustomerBookingController::class, 'reschedule']);
    Route::patch('c2/bookings/{id}/cancel', [CustomerBookingController::class, 'cancel']);
    Route::post('c2/bookings/{id}/review', [CustomerBookingController::class, 'createReview']);
    Route::post('c2/merchants/{id}/favorite', [CustomerBookingController::class, 'favoriteMerchant']);
    Route::get('c2/services/{id}/available-slots', [CustomerBookingController::class, 'availableSlots']);

    // Customers
    Route::get('customers', [CustomerController::class, 'index']);
    Route::get('customers/{id}', [CustomerController::class, 'show']);
    Route::post('customers', [CustomerController::class, 'store']);

    // Reviews
    Route::apiResource('reviews', ReviewController::class);
    Route::patch('reviews/{id}/moderate', [ReviewController::class, 'moderate']);
    Route::post('reviews/{id}/reply', [ReviewController::class, 'reply']);
    Route::post('reviews/{id}/vote', [ReviewController::class, 'vote']);

    // Contact Inquiries
    Route::get('contact', [ContactController::class, 'index']);
    Route::get('contact/{id}', [ContactController::class, 'show']);
    Route::patch('contact/{id}/read', [ContactController::class, 'markRead']);
    Route::post('contact/{id}/reply', [ContactController::class, 'reply']);
    Route::delete('contact/{id}', [ContactController::class, 'destroy']);

    // Pricing Plans
    Route::apiResource('pricing', PricingController::class);
    Route::patch('pricing/{pricing}/toggle', [PricingController::class, 'toggle']);

    // Ads
    Route::apiResource('ads', AdController::class);
    Route::patch('ads/{id}/toggle', [AdController::class, 'toggle']);
    Route::post('ads/{id}/click', [AdController::class, 'trackClick']);

    // CMS Content Pages
    Route::apiResource('content', ContentController::class);
    Route::patch('content/{id}/publish', [ContentController::class, 'togglePublish']);

    // Settlements & Financials
    Route::get('settlements', [SettlementController::class, 'index']);
    Route::post('settlements', [SettlementController::class, 'store']);
    Route::get('settlements/{id}', [SettlementController::class, 'show']);
    Route::put('settlements/{id}', [SettlementController::class, 'update']);
    Route::patch('settlements/{id}/payout', [SettlementController::class, 'processPayout']);
    Route::post('settlements/export', [SettlementController::class, 'export']);
    Route::delete('settlements/{id}', [SettlementController::class, 'destroy']);

    // Reports & Analytics
    Route::get('reports/summary',   [ReportController::class, 'summary']);
    Route::get('reports/revenue',   [ReportController::class, 'revenue']);
    Route::get('reports/bookings',  [ReportController::class, 'bookingStats']);
    Route::get('reports/merchants', [ReportController::class, 'merchantStats']);
    Route::post('reports/export',   [ReportController::class, 'export']);

    // Permissions (RBAC)
    Route::get('permissions',                      [PermissionController::class, 'list']);
    Route::get('permissions/roles',                [PermissionController::class, 'roles']);
    Route::get('permissions/roles/{roleId}',       [PermissionController::class, 'rolePermissions']);
    Route::post('permissions/roles/{roleId}/sync', [PermissionController::class, 'syncRolePerms']);

    // Platform Settings
    Route::get('settings',           [SettingController::class, 'getSettings']);
    Route::put('settings',           [SettingController::class, 'updateSettings']);
    Route::put('settings/company',   [SettingController::class, 'updateCompany']);
    Route::put('settings/password',  [SettingController::class, 'updatePassword']);

    // Notifications
    Route::get('notifications/settings',    [NotificationController::class, 'getSettings']);
    Route::put('notifications/settings',    [NotificationController::class, 'saveSettings']);
    Route::post('notifications/settings',   [NotificationController::class, 'saveSettings']);
    Route::post('notifications/test-email', [NotificationController::class, 'testEmail']);
    Route::get('notifications/logs',        [NotificationController::class, 'getLogs']);

    // Chat / Real-time Messaging
    Route::get('chat', [ChatController::class, 'index']);
    Route::post('chat', [ChatController::class, 'store']);

    // Company 2 generic endpoints
    Route::get('c2/settings', [C2PlatformController::class, 'getSettings']);
    Route::put('c2/settings', [C2PlatformController::class, 'updateSettings']);
    Route::get('c2/{type}', [C2PlatformController::class, 'index']);
    Route::post('c2/{type}', [C2PlatformController::class, 'store']);
    Route::put('c2/{type}/{id}', [C2PlatformController::class, 'update']);
    Route::delete('c2/{type}/{id}', [C2PlatformController::class, 'destroy']);

    // Company 3 Market Stocks & Stakes Intelligence
    Route::get('stocks',             [StockController::class, 'index']);
    Route::get('stocks/recommended', [StockController::class, 'recommended']);
    Route::get('stocks/lowest-ever', [StockController::class, 'lowestEver']);
    Route::post('stocks/buy',        [StockController::class, 'executeBuy']);

    // Company 4 Real Estate & Tokenized Sukuk Staking Hub
    Route::get('properties',         [RealEstateController::class, 'index']);
    Route::post('properties/invest', [RealEstateController::class, 'invest']);

    // Company 5 Developer Academy & Free Courses Hub
    Route::get('courses',            [CourseController::class, 'index']);

    // Super Admin Management
    Route::prefix('admin')->group(function () {
        Route::get('users',              [AdminController::class, 'users']);
        Route::get('users/{user}',       [AdminController::class, 'showUser']);
        Route::put('users/{user}',       [AdminController::class, 'updateUser']);
        Route::patch('users/{user}/status', [AdminController::class, 'toggleUserStatus']);
        Route::delete('users/{user}',    [AdminController::class, 'deleteUser']);
        Route::get('audit-logs',         [AdminController::class, 'auditLogs']);
        Route::get('stats',              [AdminController::class, 'globalStats']);
        Route::get('tenants',            [AdminController::class, 'tenants']);
        Route::get('domains',            [DomainController::class, 'index']);
        Route::post('domains',           [DomainController::class, 'store']);
        Route::delete('domains/{domain}',[DomainController::class, 'destroy']);
    });
});

// 404 Fallback
Route::fallback(fn() => response()->json([
    'success' => false,
    'message' => 'API endpoint not found.',
    'code'    => 404
], 404));
