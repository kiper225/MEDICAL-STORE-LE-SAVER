<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'images'])
            ->when($request->q, fn($q, $term) => $q->where('nom', 'like', "%{$term}%"))
            ->when($request->category_slug, fn($q, $slug) =>
                $q->whereHas('category', fn($c) => $c->where('slug', $slug)))
            ->when($request->type_disponibilite, fn($q, $type) => $q->where('type_disponibilite', $type))
            ->when($request->vendor_id, fn($q, $id) => $q->where('vendor_id', $id))
            ->when(!$request->boolean('all'), fn($q) => $q->where('statut', 'actif'))
            ->with(['category', 'images', 'vendor'])
            ->paginate($request->input('per_page', 20));

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_slug' => 'required|exists:categories,slug',
            'reference' => 'nullable|string|unique:products,reference',
            'type_disponibilite' => 'required|in:vente,location,les_deux',
            'necessite_installation' => 'boolean',
            'necessite_certification' => 'boolean',
            'prix_vente' => 'nullable|numeric',
            'prix_location_jour' => 'nullable|numeric',
            'prix_location_semaine' => 'nullable|numeric',
            'prix_location_mois' => 'nullable|numeric',
            'caution_location' => 'nullable|numeric',
            'poids' => 'nullable|numeric',
            'dimensions' => 'nullable|string',
        ]);

        $data['reference'] = $data['reference'] ?? 'PROD-' . strtoupper(uniqid());
        $data['vendor_id'] = $request->user()->id;

        $product = Product::create($data);

        return response()->json($product, 201);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'images', 'vendor'])
            ->loadCount('reviews')
            ->loadAvg('reviews', 'note');

        return response()->json($product);
    }


    public function destroy(Product $product)
    {
        $product->update(['statut' => 'inactif']);
        return response()->json(null, 204);
    }

    public function uploadImages(Request $request, Product $product)
    {
        $request->validate([
            'images' => 'required|array|max:5',
            'images.*' => 'image|max:4096',
        ]);

        foreach ($request->file('images') as $index => $file) {
            $path = $file->store('products', 'public');

            $product->images()->create([
                'path' => $path,
                'ordre' => $index,
            ]);
        }

        return response()->json($product->load('images'), 201);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'type_disponibilite' => 'sometimes|required|in:vente,location,les_deux',
            'necessite_installation' => 'boolean',
            'necessite_certification' => 'boolean',
            'prix_vente' => 'nullable|numeric',
            'prix_location_jour' => 'nullable|numeric',
            'prix_location_semaine' => 'nullable|numeric',
            'prix_location_mois' => 'nullable|numeric',
            'caution_location' => 'nullable|numeric',
            'poids' => 'nullable|numeric',
            'dimensions' => 'nullable|string',
            'marque' => 'nullable|string',
            'couleurs' => 'nullable|array',
            'statut' => 'sometimes|in:actif,inactif',
        ]);

        $product->update($data);

        return response()->json($product->load(['category', 'images']));
    }

    public function deleteImage(Product $product, \App\Models\ProductImage $image)
    {
        if ($image->product_id !== $product->id) {
            abort(404);
        }

        \Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json(null, 204);
    }
}