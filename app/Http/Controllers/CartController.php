<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function index(): View
    {
        $cart = $this->cartService->getDetailedItems();

        return view('public.cart.index', compact('cart'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'weight_option_id' => ['required', 'integer', 'exists:product_weight_options,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);

        $this->cartService->add(
            (int) $validated['product_id'],
            (int) $validated['weight_option_id'],
            $quantity
        );

        return redirect()->route('cart.index')
            ->with('status', 'Pilihan daging berhasil ditambahkan ke keranjang belanja.');
    }

    public function update(Request $request, int $weightOptionId): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:50'],
        ]);

        $this->cartService->update($weightOptionId, (int) $validated['quantity']);

        return redirect()->route('cart.index')
            ->with('status', 'Jumlah pesanan berhasil diperbarui.');
    }

    public function destroy(int $weightOptionId): RedirectResponse
    {
        $this->cartService->remove($weightOptionId);

        return redirect()->route('cart.index')
            ->with('status', 'Item berhasil dihapus dari keranjang.');
    }

    public function clear(): RedirectResponse
    {
        $this->cartService->clear();

        return redirect()->route('cart.index')
            ->with('status', 'Keranjang belanja berhasil dikosongkan.');
    }
}
