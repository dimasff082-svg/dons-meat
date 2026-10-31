<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'activeWeightOptions'])
            ->where('is_active', true);

        // Filter by category
        $selectedCategory = null;
        if ($request->filled('kategori')) {
            $selectedCategory = Category::where('slug', $request->string('kategori'))->first();
            if ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
            }
        }

        // Search by keyword
        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            });
        }

        // Sorting
        $sort = $request->string('sort', 'terbaru')->toString();
        match ($sort) {
            'harga_terendah' => $query->orderBy('price_per_kg', 'asc'),
            'harga_tertinggi' => $query->orderBy('price_per_kg', 'desc'),
            default => $query->latest('id'),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = Category::where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('public.catalog.index', compact('products', 'categories', 'selectedCategory'));
    }

    public function show(string $slug): View
    {
        $product = Product::with(['category', 'activeWeightOptions'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $relatedProducts = Product::with(['category', 'activeWeightOptions'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->take(4)
            ->get();

        return view('public.catalog.show', compact('product', 'relatedProducts'));
    }
}
