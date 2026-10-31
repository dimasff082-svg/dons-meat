<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPriceHistory;
use App\Models\ProductWeightOption;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'weightOptions'])->latest('id');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('low_stock')) {
            $query->whereRaw('(stock_gram - reserved_stock_gram) <= low_stock_threshold_gram');
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['nullable', 'string', 'max:50', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'price_per_kg' => ['required', 'numeric', 'min:0'],
            'stock_gram' => ['required', 'integer', 'min:0'],
            'low_stock_threshold_gram' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'default_weights' => ['nullable', 'array'],
            'default_weights.*' => ['integer', 'min:50'],
        ]);

        if (empty($validated['sku'])) {
            $validated['sku'] = 'DM-'.strtoupper(Str::random(6));
        }

        if (empty($validated['slug'])) {
            $baseSlug = Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image_path'] = $path;
        }

        DB::transaction(function () use ($validated, $request) {
            $productData = collect($validated)->except(['image', 'default_weights'])->toArray();
            $productData['reserved_stock_gram'] = 0;

            $product = Product::create($productData);

            // Record initial price history
            ProductPriceHistory::create([
                'product_id' => $product->id,
                'old_price_per_kg' => 0,
                'new_price_per_kg' => $product->price_per_kg,
                'changed_by_user_id' => auth()->id(),
                'reason' => 'Harga awal saat produk dibuat',
            ]);

            // Record initial stock movement if physical stock exists
            if ($product->stock_gram > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'order_id' => null,
                    'created_by_user_id' => auth()->id(),
                    'movement_type' => StockMovementType::Inbound,
                    'stock_delta_gram' => $product->stock_gram,
                    'reserved_delta_gram' => 0,
                    'stock_balance_after_gram' => $product->stock_gram,
                    'reserved_balance_after_gram' => 0,
                    'reason' => 'Stok awal saat pendaftaran produk baru',
                ]);
            }

            // Create weight options
            $weights = $request->input('default_weights', [250, 500, 1000]);
            if (empty($weights)) {
                $weights = [250, 500, 1000];
            }

            sort($weights);
            foreach ($weights as $index => $weightGram) {
                $weightGram = (int) $weightGram;
                $label = $weightGram >= 1000
                    ? ($weightGram / 1000).' kg'
                    : $weightGram.' g';

                ProductWeightOption::create([
                    'product_id' => $product->id,
                    'weight_gram' => $weightGram,
                    'label' => $label,
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }
        });

        return redirect()->route('admin.products.index')
            ->with('status', 'Produk berhasil dibuat.');
    }

    public function edit(Product $product): View
    {
        $product->load(['category', 'weightOptions', 'priceHistories.changedByUser']);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products')->ignore($product->id)],
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('products')->ignore($product->id)],
            'description' => ['nullable', 'string'],
            'price_per_kg' => ['required', 'numeric', 'min:0'],
            'low_stock_threshold_gram' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'price_change_reason' => ['nullable', 'string', 'max:255'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if ($request->hasFile('image')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($validated, $request, $product) {
            $oldPrice = (float) $product->price_per_kg;
            $newPrice = (float) $validated['price_per_kg'];

            if ($oldPrice !== $newPrice) {
                ProductPriceHistory::create([
                    'product_id' => $product->id,
                    'old_price_per_kg' => $oldPrice,
                    'new_price_per_kg' => $newPrice,
                    'changed_by_user_id' => auth()->id(),
                    'reason' => $request->input('price_change_reason') ?: 'Pembaruan harga oleh admin/pemilik',
                ]);
            }

            $productData = collect($validated)->except(['image', 'price_change_reason'])->toArray();
            $product->update($productData);
        });

        return redirect()->route('admin.products.index')
            ->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('status', 'Produk berhasil dihapus (soft delete). Riwayat order tetap tersimpan.');
    }
}
