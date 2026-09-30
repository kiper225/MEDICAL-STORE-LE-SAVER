<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{User, Product, Order, Installation};
use App\Http\Controllers\Api\{
    AuthController, ProductController, CategoryController,
    RentalController, InstallationController, OrderController, PaymentController,
    TechnicianController, UserController, AdminController
};

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'total_users' => User::count(),
            'total_products' => Product::where('statut', 'actif')->count(),
            'total_orders' => Order::count(),
            'total_installations' => Installation::count(),
            'installations_en_attente' => Installation::whereNull('technicien_id')->count(),
            'recent_orders' => Order::with('user')->latest()->limit(5)->get(),
        ]);
    }
}