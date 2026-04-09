<?php
// backend/routes/api.php
// Complete API route definitions for Company 2

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MerchantController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\PricingController;
use App\Http\Controllers\Api\AdController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\SettlementController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DomainController;

// ─────────────────────────────────────────
// Public routes (no auth required)
// ─────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('google',   [AuthController::class, 'googleLogin']);
});

// Domain validation (called by frontend on load)
Route::get('domain/check', [DomainController::class, 'check']);

// ─────────────────────────────────────────
// Authenticated routes
// ─────────────────────────────────────────
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {

    // Auth
    Route::get('auth/me',     [AuthController::class, 'me']);
    Route::post('auth/logout',[AuthController::class, 'logout']);

    // Merchants – requires permission check
    Route::apiResource('merchants', MerchantController::class);
    Route::patch('merchants/{merchant}/status', [MerchantController::class, 'updateStatus']);

    // Categories
    Route::apiResource('categories', CategoryController::class);
    Route::post('categories/reorder', [CategoryController::class, 'reorder']);

    // Services
    Route::apiResource('services', ServiceController::class);
    Route::patch('services/{service}/toggle', [ServiceController::class, 'toggle']);

    // Bookings
    Route::apiResource('bookings', BookingController::class);
    Route::patch('bookings/{booking}/status', [BookingController::class, 'updateStatus']);
    Route::get('bookings/calendar/{year}/{month}', [BookingController::class, 'calendar']);

    // Reviews
    Route::apiResource('reviews', ReviewController::class);
    Route::patch('reviews/{review}/moderate', [ReviewController::class, 'moderate']);

    // Contact Messages
    Route::apiResource('contact', ContactController::class)->except(['update']);
    Route::patch('contact/{message}/read', [ContactController::class, 'markRead']);

    // Pricing Plans
    Route::apiResource('pricing', PricingController::class);
    Route::patch('pricing/{plan}/toggle', [PricingController::class, 'toggle']);

    // Ads
    Route::apiResource('ads', AdController::class);
    Route::patch('ads/{ad}/toggle', [AdController::class, 'toggle']);

    // Content Pages
    Route::apiResource('content', ContentController::class);
    Route::patch('content/{page}/publish', [ContentController::class, 'togglePublish']);

    // Settlements
    Route::apiResource('settlements', SettlementController::class)->except(['create','edit','destroy']);
    Route::post('settlements/export', [SettlementController::class, 'export']);

    // Reports
    Route::get('reports/summary',     [ReportController::class, 'summary']);
    Route::get('reports/revenue',     [ReportController::class, 'revenue']);
    Route::get('reports/bookings',    [ReportController::class, 'bookingStats']);
    Route::get('reports/merchants',   [ReportController::class, 'merchantStats']);
    Route::post('reports/export',     [ReportController::class, 'export']);

    // Permissions (RBAC management)
    Route::get('permissions',                    [PermissionController::class, 'index']);
    Route::get('permissions/roles',              [PermissionController::class, 'roles']);
    Route::get('permissions/roles/{role}',       [PermissionController::class, 'rolePermissions']);
    Route::post('permissions/roles/{role}/sync', [PermissionController::class, 'syncRolePermissions']);

    // Notification Settings
    Route::get('notifications/settings',         [NotificationController::class, 'index']);
    Route::put('notifications/settings',         [NotificationController::class, 'update']);

    // System Settings
    Route::get('settings',         [SettingController::class, 'index']);
    Route::put('settings',         [SettingController::class, 'update']);
    Route::put('settings/company', [SettingController::class, 'updateCompany']);
    Route::put('settings/password',[SettingController::class, 'updatePassword']);

    // ─────────────────────────────────────────
    // Admin-only routes (is_super_admin = 1)
    // ─────────────────────────────────────────
    Route::middleware('admin')->prefix('admin')->group(function () {
        // User management
        Route::get('users',                     [AdminController::class, 'users']);
        Route::get('users/{user}',              [AdminController::class, 'showUser']);
        Route::put('users/{user}',              [AdminController::class, 'updateUser']);
        Route::patch('users/{user}/status',     [AdminController::class, 'toggleUserStatus']);
        Route::delete('users/{user}',           [AdminController::class, 'deleteUser']);

        // Override any record
        Route::put('merchants/{merchant}/force', [AdminController::class, 'forceMerchantUpdate']);
        Route::put('bookings/{booking}/force',   [AdminController::class, 'forceBookingUpdate']);

        // Audit logs
        Route::get('audit-logs',                [AdminController::class, 'auditLogs']);

        // Global stats (across all tenants)
        Route::get('stats',                     [AdminController::class, 'globalStats']);
        Route::get('tenants',                   [AdminController::class, 'tenants']);

        // Domain management
        Route::get('domains',                   [DomainController::class, 'index']);
        Route::post('domains',                  [DomainController::class, 'store']);
        Route::delete('domains/{domain}',       [DomainController::class, 'destroy']);
    });
});

// 404 fallback for API
Route::fallback(fn() => response()->json([
    'success' => false,
    'message' => 'API endpoint not found.',
    'code'    => 404
], 404));
