<?php

use App\Http\Controllers\Api\{
    AuthController, ProductController, CategoryController,
    RentalController, InstallationController, OrderController, PaymentController,
    TechnicianController, UserController, AdminController, ReviewController
};
use Illuminate\Support\Facades\Route;

Route::apiResource('rentals', RentalController::class);
Route::apiResource('installations', InstallationController::class);
Route::apiResource('technicians', TechnicianController::class);
Route::apiResource('users', UserController::class);
// Callback public, Intouch doit pouvoir l'appeler sans authentification
Route::post('/payments/intouch/callback', [PaymentController::class, 'callback']);

// --- Public ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product:slug}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products/{product:slug}/reviews', [ReviewController::class, 'index']);

// dans le groupe auth:sanctum
Route::post('/products/{product:slug}/reviews', [ReviewController::class, 'store']);

Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// --- Authentifié (tous rôles confondus) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/products/{product:slug}/reviews', [ReviewController::class, 'store']);
    
    Route::apiResource('orders', OrderController::class)->except(['destroy']);
    Route::apiResource('rentals', RentalController::class);
    Route::post('/payments', [PaymentController::class, 'store']);

    Route::post('/profile', [AuthController::class, 'updateProfile']);

    // --- Client uniquement ---
    Route::middleware('role:client')->group(function () {
        Route::get('/my-orders', [OrderController::class, 'myOrders']);
        Route::get('/my-rentals', [RentalController::class, 'myRentals']);
    });

    // --- Vendeur ---
    Route::middleware('role:vendeur,admin')->group(function () {
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
        Route::apiResource('categories', CategoryController::class)->except(['index']);
        Route::get('/vendor/orders', [OrderController::class, 'vendorOrders']);
        Route::post('/products/{product}/images', [ProductController::class, 'uploadImages']);
        Route::delete('/products/{product}/images/{image}', [ProductController::class, 'deleteImage']);
        Route::get('/vendor/dashboard', [OrderController::class, 'vendorDashboard']);
        Route::put('/order-items/{orderItem}/statut', [OrderController::class, 'updateItemStatus']);
    });

    // --- Technicien ---
    Route::middleware('role:technicien,admin')->group(function () {
        Route::apiResource('installations', InstallationController::class);
        Route::get('/my-installations', [InstallationController::class, 'myInstallations']);
        Route::get('/technicien/dashboard', [InstallationController::class, 'technicienDashboard']);
    });

    // --- Admin uniquement ---
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
        Route::apiResource('users', UserController::class)->except(['store', 'show']);
        Route::get('/technicians', [TechnicianController::class, 'index']);
        Route::post('/technicians', [TechnicianController::class, 'store']);
        Route::delete('/technicians/{technician}', [TechnicianController::class, 'destroy']);
    });

    // Dans le groupe auth:sanctum
    Route::post('/payments/mobile-money', [PaymentController::class, 'initiateMobileMoney']);
    Route::get('/payments/{payment}/status', [PaymentController::class, 'checkStatus']);
    
});
