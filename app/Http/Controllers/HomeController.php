<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $setting = StoreSetting::current();

        $categories = Category::where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $featuredProducts = Product::with(['category', 'activeWeightOptions'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->latest('id')
            ->take(6)
            ->get();

        return view('public.home', compact('setting', 'categories', 'featuredProducts'));
    }
}
