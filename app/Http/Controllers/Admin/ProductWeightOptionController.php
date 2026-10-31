<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductWeightOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductWeightOptionController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'weight_gram' => [
                'required',
                'integer',
                'min:50',
                Rule::unique('product_weight_options')->where('product_id', $product->id),
            ],
            'label' => ['nullable', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $weightGram = (int) $validated['weight_gram'];
        if (empty($validated['label'])) {
            $validated['label'] = $weightGram >= 1000
                ? ($weightGram / 1000).' kg'
                : $weightGram.' g';
        }

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['product_id'] = $product->id;

        ProductWeightOption::create($validated);

        return back()->with('status', 'Pilihan berat baru berhasil ditambahkan.');
    }

    public function update(Request $request, Product $product, ProductWeightOption $weightOption): RedirectResponse
    {
        if ($weightOption->product_id !== $product->id) {
            abort(404);
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $weightOption->update($validated);

        return back()->with('status', 'Pilihan berat berhasil diperbarui.');
    }

    public function destroy(Product $product, ProductWeightOption $weightOption): RedirectResponse
    {
        if ($weightOption->product_id !== $product->id) {
            abort(404);
        }

        $weightOption->delete();

        return back()->with('status', 'Pilihan berat berhasil dihapus.');
    }
}
