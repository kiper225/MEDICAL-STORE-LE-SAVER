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
            $prix = $product->en_promo ? $product->prix_promo : $product->prix_vente;
            $total += $prix * $item['quantite'];
        }

        $order = Order::create([
            'user_id' => $request->user()->id,
            'statut' => 'en_attente',
            'total' => $total,
            'mode_paiement' => $data['mode_paiement'],
        ]);

        foreach ($data['items'] as $item) {
            $product = \App\Models\Product::findOrFail($item['product_id']);
            $prix = $product->en_promo ? $product->prix_promo : $product->prix_vente;

            $order->items()->create([
                'product_id' => $product->id,
                'quantite' => $item['quantite'],
                'prix_unitaire' => $prix,
            ]);
        }

        $order->items()->with('product')->get()
            ->pluck('product.vendor_id')
            ->unique()
            ->filter()
            ->each(function ($vendorId) use ($order) {
                $vendor = \App\Models\User::find($vendorId);
                $vendor?->notify(new \App\Notifications\NewOrderNotification($order));
            });

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

    public function vendorDashboard(Request $request)
    {
        $vendorId = $request->user()->id;
        $year = now()->year;

        $orderIds = \App\Models\OrderItem::whereHas('product', fn($q) => $q->where('vendor_id', $vendorId))
            ->pluck('order_id')->unique();

        $totalRevenue = \App\Models\OrderItem::whereHas('product', fn($q) => $q->where('vendor_id', $vendorId))
            ->whereHas('order', fn($q) => $q->whereIn('statut', ['paye', 'livre']))
            ->selectRaw('SUM(quantite * prix_unitaire) as total')->value('total') ?? 0;

        $ventesParMois = \App\Models\OrderItem::whereHas('product', fn($q) => $q->where('vendor_id', $vendorId))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.statut', ['paye', 'livre'])
            ->whereYear('orders.created_at', $year)
            ->selectRaw('MONTH(orders.created_at) as mois, SUM(order_items.quantite * order_items.prix_unitaire) as total')
            ->groupBy('mois')
            ->pluck('total', 'mois');

        return response()->json([
            'total_products' => Product::where('vendor_id', $vendorId)->where('statut', 'actif')->count(),
            'total_orders' => $orderIds->count(),
            'total_revenue' => $totalRevenue,
            'monthly_sales' => collect(range(1, 12))->map(fn($m) => (float) ($ventesParMois[$m] ?? 0)),
            'recent_orders' => Order::whereIn('id', $orderIds)->with(['user', 'items.product'])->latest()->limit(6)->get(),
        ]);
    }

    public function updateItemStatus(Request $request, \App\Models\OrderItem $orderItem)
    {
        if ($request->user()->role === 'vendeur' && $orderItem->product->vendor_id !== $request->user()->id) {
            abort(403, 'Cette ligne de commande ne vous appartient pas.');
        }

        $data = $request->validate([
            'statut' => 'required|in:en_preparation,expedie,livre,annule',
        ]);

        $orderItem->update($data);
        $orderItem->order->user->notify(new \App\Notifications\OrderItemStatusNotification($orderItem));

        return response()->json($orderItem->load('product'));
    }
}
