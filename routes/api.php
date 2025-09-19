<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
    Route::post('password/reset',  [AuthController::class, 'resetPassword']);
});

// Public image route - Enhanced with better error handling
Route::get('image/{path}', function ($path) {
    try {
        // Security: Prevent directory traversal
        $path = str_replace(['../', '..\\'], '', $path);
        
        $fullPath = storage_path('app/public/' . $path);
        
        // Log for debugging
        \Log::info('Image request:', [
            'requested_path' => $path,
            'full_path' => $fullPath,
            'exists' => file_exists($fullPath)
        ]);
        
        if (!file_exists($fullPath)) {
            \Log::warning('Image not found:', ['path' => $fullPath]);
            return response()->json([
                'error' => 'Image not found',
                'path' => $path,
                'debug' => 'File does not exist: ' . $fullPath
            ], 404);
        }
        
        $file = file_get_contents($fullPath);
        $mimeType = mime_content_type($fullPath) ?: 'image/jpeg';
        
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
            
    } catch (\Exception $e) {
        \Log::error('Image route error:', [
            'path' => $path,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'error' => 'Failed to serve image',
            'message' => $e->getMessage()
        ], 500);
    }
})->where('path', '.*');

// Alternative image route for direct storage access (for backward compatibility)
Route::get('storage/{path}', function ($path) {
    try {
        // Security: Prevent directory traversal
        $path = str_replace(['../', '..\\'], '', $path);
        
        $fullPath = storage_path('app/public/' . $path);
        
        // Log for debugging
        \Log::info('Storage image request:', [
            'requested_path' => $path,
            'full_path' => $fullPath,
            'exists' => file_exists($fullPath)
        ]);
        
        if (!file_exists($fullPath)) {
            \Log::warning('Storage image not found:', ['path' => $fullPath]);
            return response()->json([
                'error' => 'Image not found',
                'path' => $path,
                'debug' => 'File does not exist: ' . $fullPath
            ], 404);
        }
        
        $file = file_get_contents($fullPath);
        $mimeType = mime_content_type($fullPath) ?: 'image/jpeg';
        
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET')
            ->header('Access-Control-Allow-Headers', 'Content-Type');
            
    } catch (\Exception $e) {
        \Log::error('Storage image route error:', [
            'path' => $path,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'error' => 'Failed to serve image',
            'message' => $e->getMessage()
        ], 500);
    }
})->where('path', '.*');

// Public menu routes
Route::prefix('menu')->group(function () {
    Route::get('/', [MenuController::class, 'getMenu']);
    Route::get('tables', [ManagerController::class, 'getTables']);
    Route::get('categories', [MenuController::class, 'getCategories']);
    Route::get('categories/{id}/dishes', [MenuController::class, 'getDishesByCategory']);
    Route::get('dishes/{id}', [MenuController::class, 'getDish']);
    Route::get('search', [MenuController::class, 'searchDishes']);
    Route::get('popular', [MenuController::class, 'getPopularDishes']);
    Route::get('highlights', [MenuController::class, 'getMenuHighlights']);
    Route::get('dietary/{preference}', [MenuController::class, 'getDishesByDietaryPreference']);
    Route::get('recommendations', [MenuController::class, 'getRecommendations']);
    Route::get('discounts', [MenuController::class, 'getDiscounts']);
    Route::get('dishes/{id}/reviews', [MenuController::class, 'getDishReviews']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('change-locale', [AuthController::class, 'changeLocale']);
        Route::post('profile', [AuthController::class, 'updateProfile']);
        Route::put('phone', [AuthController::class, 'updatePhone']);
        Route::put('password', [AuthController::class, 'changePassword']);
        Route::post('email/verification-notification', [AuthController::class, 'sendEmailVerification']);
        Route::get('email/verify', [AuthController::class, 'verifyEmail']);
    });

    // Admin routes
    Route::prefix('admin')->middleware('role:admin,manager,cashier')->group(function () {
        Route::get('users', [AdminController::class, 'getUsers']);
        Route::get('users/{id}', [AdminController::class, 'getUser']);
        Route::post('users', [AdminController::class, 'createUser']);
        Route::put('users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('users/{id}', [AdminController::class, 'deleteUser']);
        
        Route::get('roles', [AdminController::class, 'getRoles']);
        Route::post('roles', [AdminController::class, 'createRole']);
        Route::put('roles/{id}', [AdminController::class, 'updateRole']);
        Route::delete('roles/{id}', [AdminController::class, 'deleteRole']);
        
        Route::get('stats', [AdminController::class, 'getSystemStats']);
    });

    // Manager routes
    Route::prefix('manager')->middleware('role:admin,manager,cashier')->group(function () {
        // Table management
        Route::get('tables', [ManagerController::class, 'getTables']);
        Route::post('tables', [ManagerController::class, 'createTable']);
        Route::put('tables/{id}', [ManagerController::class, 'updateTable']);
        Route::delete('tables/{id}', [ManagerController::class, 'deleteTable']);
        
        // Reservation management
        Route::get('reservations', [ManagerController::class, 'getReservations']);
        Route::put('reservations/{id}/status', [ManagerController::class, 'updateReservationStatus']);
        
        // Category management
        Route::get('categories', [ManagerController::class, 'getCategories']);
        Route::post('categories', [ManagerController::class, 'createCategory']);
        Route::put('categories/{id}', [ManagerController::class, 'updateCategory']);
        Route::delete('categories/{id}', [ManagerController::class, 'deleteCategory']);
        
        // Dish management
        Route::get('dishes', [ManagerController::class, 'getDishes']);
        Route::post('dishes', [ManagerController::class, 'createDish']);
        Route::put('dishes/{id}', [ManagerController::class, 'updateDish']);
        Route::delete('dishes/{id}', [ManagerController::class, 'deleteDish']);
        
        // Discount management
        Route::get('dishes/discounts', [ManagerController::class, 'getDishesWithDiscounts']);
        Route::post('dishes/{id}/discount', [ManagerController::class, 'applyDishDiscount']);
        Route::delete('dishes/{id}/discount', [ManagerController::class, 'removeDishDiscount']);
    });

    // Cashier routes
    Route::prefix('cashier')->middleware('role:admin,manager,cashier')->group(function () {
        Route::get('orders', [CashierController::class, 'getOrders']);
        Route::get('orders/needing-attention', [CashierController::class, 'getOrdersNeedingAttention']);
        Route::get('orders/today-summary', [CashierController::class, 'getTodayOrdersSummary']);
        Route::get('orders/{id}', [CashierController::class, 'getOrder']);
        Route::put('orders/{id}/status', [CashierController::class, 'updateOrderStatus']);
        Route::put('orders/{orderId}/items/{itemId}/status', [CashierController::class, 'updateOrderItemStatus']);
        Route::put('orders/{id}/delivered', [CashierController::class, 'markOrderDelivered']);
        Route::put('orders/{id}/cancel', [CashierController::class, 'cancelOrder']);
        Route::get('orders/table/{tableId}', [CashierController::class, 'getOrdersByTable']);
        Route::get('orders/user/{userId}', [CashierController::class, 'getOrdersByUser']);
    });

    // Recent activities (admin, manager, cashier)
    Route::prefix('activity')->middleware('role:admin,manager,cashier')->group(function () {
        Route::get('recent', [ActivityController::class, 'getRecentActivities']);
    });

    // Customer routes
    Route::prefix('customer')->middleware('role:customer')->group(function () {
        // Table reservations
        Route::get('tables/available', [CustomerController::class, 'getAvailableTables']);
        Route::post('reservations', [CustomerController::class, 'createReservation']);
        Route::get('reservations', [CustomerController::class, 'getMyReservations']);
        Route::put('reservations/{id}/cancel', [CustomerController::class, 'cancelReservation']);
        
        // Orders
        Route::get('dishes', [CustomerController::class, 'getAvailableDishes']);
        Route::post('orders', [CustomerController::class, 'createOrder']);
        Route::get('orders', [CustomerController::class, 'getMyOrders']);
        Route::get('orders/{id}', [CustomerController::class, 'getOrder']);
        Route::put('orders/{id}/cancel', [CustomerController::class, 'cancelOrder']);
        Route::get('orders/{id}/track', [CustomerController::class, 'trackOrder']);
        
        // Favorites
        Route::get('favorites', [CustomerController::class, 'getFavorites']);
        Route::post('favorites', [CustomerController::class, 'addToFavorites']);
        Route::delete('favorites/{dishId}', [CustomerController::class, 'removeFromFavorites']);
        Route::post('favorites/toggle', [CustomerController::class, 'toggleFavorite']);
        
        // Reviews
        Route::post('reviews', [MenuController::class, 'submitReview']);
    });
});
