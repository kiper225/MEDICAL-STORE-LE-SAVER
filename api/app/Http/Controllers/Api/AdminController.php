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
        $year = now()->year;

        $ventesParMois = Order::whereYear('created_at', $year)
            ->whereIn('statut', ['paye', 'livre'])
            ->selectRaw('MONTH(created_at) as mois, SUM(total) as total')
            ->groupBy('mois')
            ->pluck('total', 'mois');

        $commandesParMois = Order::whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as mois, COUNT(*) as total')
            ->groupBy('mois')
            ->pluck('total', 'mois');

        return response()->json([
            'total_users' => User::count(),
            'total_products' => Product::where('statut', 'actif')->count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::whereIn('statut', ['paye', 'livre'])->sum('total'),
            'installations_en_attente' => Installation::whereNull('technicien_id')->count(),
            'monthly_sales' => collect(range(1, 12))->map(fn($m) => (float) ($ventesParMois[$m] ?? 0)),
            'monthly_orders' => collect(range(1, 12))->map(fn($m) => (int) ($commandesParMois[$m] ?? 0)),
            'recent_orders' => Order::with('user')->latest()->limit(6)->get(),
        ]);
    }
}