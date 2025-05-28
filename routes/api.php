<?php

use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Api\Admin\BookingController as AdminBookingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check endpoint
Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Sheba Platform API is running',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString()
    ]);
});

// Public API routes
Route::prefix('v1')->group(function () {

    // Service endpoints
    Route::prefix('services')->group(function () {
        Route::get('/', [ServiceController::class, 'index']);
        Route::get('/categories', [ServiceController::class, 'categories']);
        Route::get('/category/{category:slug}', [ServiceController::class, 'byCategory']);
        Route::get('/{service:slug}', [ServiceController::class, 'show']);
    });

    // Booking endpoints
    Route::prefix('bookings')->group(function () {
        Route::post('/', [BookingController::class, 'store']);
        Route::get('/{booking:booking_id}', [BookingController::class, 'show']);
        Route::get('/{booking:booking_id}/status', [BookingController::class, 'status']);
        Route::patch('/{booking:booking_id}/cancel', [BookingController::class, 'cancel']);
    });

    // Admin routes
    Route::prefix('admin')->group(function () {
        // Authentication
        Route::post('login', [AdminAuthController::class, 'login']);
        Route::post('register', [AdminAuthController::class, 'register']); // Only for development

        // Protected admin routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AdminAuthController::class, 'logout']);
            Route::get('profile', [AdminAuthController::class, 'profile']);

            // Service management
            Route::apiResource('services', AdminServiceController::class);
            Route::patch('services/{service}/toggle-status', [AdminServiceController::class, 'toggleStatus']);

            // Booking management
            Route::get('bookings', [AdminBookingController::class, 'index']);
            Route::get('bookings/{booking}', [AdminBookingController::class, 'show']);
            Route::patch('bookings/{booking}/status', [AdminBookingController::class, 'updateStatus']);
            Route::get('dashboard/stats', [AdminBookingController::class, 'dashboardStats']);
        });
    });
});

// Fallback route for undefined API endpoints
Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'API endpoint not found'
    ], 404);
});
