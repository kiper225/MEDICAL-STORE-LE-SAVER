<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('children')
            ->whereNull('parent_id')
            ->get();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $data['slug'] = \Illuminate\Support\Str::slug($data['nom']);

        $category = Category::create($data);

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return response()->json($category->load('children'));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }
}