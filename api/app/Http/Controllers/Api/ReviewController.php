<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Product, ProductReview};
use Illuminate\Http\Request;
use App\Http\Controllers\Api\{
    AuthController, ProductController, CategoryController,
    RentalController, InstallationController, OrderController, PaymentController,
    TechnicianController, UserController, AdminController, ReviewController
};

class ReviewController extends Controller
{
    public function index(Product $product)
    {
        return response()->json(
            $product->reviews()->with('user')->latest()->paginate(10)
        );
    }

    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'note' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string|max:1000',
        ]);

        $review = $product->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return response()->json($review->load('user'), 201);
    }
}