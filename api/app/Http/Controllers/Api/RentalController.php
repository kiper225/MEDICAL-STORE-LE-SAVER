<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRentalRequest;
use App\Models\Rental;
use App\Models\Product;
use App\Enums\RentalStatus;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    public function index(Request $request)
    {
        $rentals = Rental::with(['product', 'user'])
            ->when($request->user()->role === 'client', fn($q) =>
                $q->where('user_id', $request->user()->id))
            ->when($request->statut, fn($q, $statut) =>
                $q->where('statut', $statut))
            ->latest()
            ->paginate(20);

        return response()->json($rentals);
    }

    public function store(StoreRentalRequest $request)
    {
        $product = Product::findOrFail($request->product_id);

        // Vérifie que le produit est bien louable
        if (!in_array($product->type_disponibilite->value, ['location', 'les_deux'])) {
            return response()->json(['message' => 'Ce produit n\'est pas disponible à la location.'], 422);
        }

        // Vérifie la disponibilité sur la période demandée
        $conflit = Rental::where('product_id', $product->id)
            ->whereIn('statut', [RentalStatus::Reserve, RentalStatus::EnCours])
            ->where(function ($q) use ($request) {
                $q->whereBetween('date_debut', [$request->date_debut, $request->date_fin])
                  ->orWhereBetween('date_fin', [$request->date_debut, $request->date_fin]);
            })
            ->exists();

        if ($conflit) {
            return response()->json(['message' => 'Produit déjà réservé sur cette période.'], 409);
        }

        $jours = now()->parse($request->date_debut)->diffInDays($request->date_fin) ?: 1;
        $montantTotal = $jours * $product->prix_location_jour;

        $rental = Rental::create([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
            'warehouse_id' => $request->warehouse_id,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'statut' => RentalStatus::Reserve,
            'montant_caution' => $product->caution_location,
            'montant_total' => $montantTotal,
        ]);

        return response()->json($rental->load('product'), 201);
    }

    public function show(Rental $rental)
    {
        return response()->json($rental->load(['product', 'user', 'installation']));
    }

    public function update(Request $request, Rental $rental)
    {
        $rental->update($request->only(['statut', 'date_retour_effective']));
        return response()->json($rental);
    }

    public function destroy(Rental $rental)
    {
        $rental->update(['statut' => RentalStatus::Annule]);
        return response()->json(null, 204);
    }
    
    public function myRentals(Request $request)
    {
        $rentals = Rental::with('product')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($rentals);
    }
}