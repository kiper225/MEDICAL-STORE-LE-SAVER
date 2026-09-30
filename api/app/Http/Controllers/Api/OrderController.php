<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['items.product', 'user'])
            ->when($request->user()->role === 'client', fn($q) =>
                $q->where('user_id', $request->user()->id))
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantite' => 'required|integer|min:1',
            'mode_paiement' => 'required|in:mobile_money,cod',
        ]);

        $total = 0;
        foreach ($data['items'] as $item) {
            $product = \App\Models\Product::findOrFail($item['product_id']);
            $total += $product->prix_vente * $item['quantite'];
        }

        $order = Order::create([
            'user_id' => $request->user()->id,
            'statut' => 'en_attente',
            'total' => $total,
            'mode_paiement' => $data['mode_paiement'],
        ]);

        foreach ($data['items'] as $item) {
            $product = \App\Models\Product::findOrFail($item['product_id']);
            $order->items()->create([
                'product_id' => $product->id,
                'quantite' => $item['quantite'],
                'prix_unitaire' => $product->prix_vente,
            ]);
        }

        return response()->json($order->load('items.product'), 201);
    }

    public function show(Order $order)
    {
        return response()->json($order->load(['items.product', 'installation']));
    }

    public function update(Request $request, Order $order)
    {
        $order->update($request->only(['statut']));
        return response()->json($order);
    }

    public function myOrders(Request $request)
    {
        $orders = Order::with('items.product')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    public function vendorOrders(Request $request)
    {
        // Simplifié : toutes les commandes contenant un produit du vendeur connecté
        $orders = Order::with('items.product')
            ->whereHas('items.product', fn($q) =>
                $q->where('vendor_id', $request->user()->id))
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }
}
