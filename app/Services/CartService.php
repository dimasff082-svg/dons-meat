<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductWeightOption;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    protected const SESSION_KEY = 'dons_meat_cart';

    public function getRawItems(): array
    {
        return session()->get(self::SESSION_KEY, []);
    }

    public function getCount(): int
    {
        $items = $this->getRawItems();

        return array_reduce($items, fn ($sum, $item) => $sum + ($item['quantity'] ?? 0), 0);
    }

    public function add(int $productId, int $weightOptionId, int $quantity = 1): array
    {
        $quantity = max(1, $quantity);

        $product = Product::active()->find($productId);
        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => 'Produk daging tidak ditemukan atau sedang tidak aktif.',
            ]);
        }

        $weightOption = ProductWeightOption::active()
            ->where('product_id', $productId)
            ->find($weightOptionId);

        if (! $weightOption) {
            throw ValidationException::withMessages([
                'weight_option_id' => 'Pilihan berat tidak valid atau sudah tidak tersedia.',
            ]);
        }

        $cart = $this->getRawItems();

        if (isset($cart[$weightOptionId])) {
            $cart[$weightOptionId]['quantity'] += $quantity;
        } else {
            $cart[$weightOptionId] = [
                'product_id' => $productId,
                'weight_option_id' => $weightOptionId,
                'quantity' => $quantity,
            ];
        }

        session()->put(self::SESSION_KEY, $cart);

        return $cart;
    }

    public function update(int $weightOptionId, int $quantity): array
    {
        $cart = $this->getRawItems();

        if ($quantity <= 0) {
            unset($cart[$weightOptionId]);
        } elseif (isset($cart[$weightOptionId])) {
            $cart[$weightOptionId]['quantity'] = $quantity;
        }

        session()->put(self::SESSION_KEY, $cart);

        return $cart;
    }

    public function remove(int $weightOptionId): array
    {
        $cart = $this->getRawItems();
        unset($cart[$weightOptionId]);
        session()->put(self::SESSION_KEY, $cart);

        return $cart;
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Get detailed items with fresh database information, prices, and stock validations.
     *
     * @return array{items: Collection, subtotal: float, total_weight_gram: int, total_quantity: int, has_unavailable: bool}
     */
    public function getDetailedItems(): array
    {
        $rawItems = $this->getRawItems();
        $detailedItems = collect();
        $subtotal = 0.0;
        $totalWeightGram = 0;
        $totalQuantity = 0;
        $hasUnavailable = false;

        if (empty($rawItems)) {
            return [
                'items' => $detailedItems,
                'subtotal' => 0.0,
                'total_weight_gram' => 0,
                'total_quantity' => 0,
                'has_unavailable' => false,
            ];
        }

        // Group required weight per product to validate overall available stock
        $productDemands = [];
        foreach ($rawItems as $raw) {
            $option = ProductWeightOption::find($raw['weight_option_id']);
            if ($option) {
                $pid = $option->product_id;
                $productDemands[$pid] = ($productDemands[$pid] ?? 0) + ($option->weight_gram * $raw['quantity']);
            }
        }

        foreach ($rawItems as $weightOptionId => $raw) {
            $quantity = (int) ($raw['quantity'] ?? 1);
            $weightOption = ProductWeightOption::with(['product.category'])->find($weightOptionId);

            if (! $weightOption || ! $weightOption->product) {
                $hasUnavailable = true;

                continue;
            }

            $product = $weightOption->product;
            $isActive = $product->is_active && $weightOption->is_active;

            $packageWeightGram = (int) $weightOption->weight_gram;
            $lineWeightGram = $packageWeightGram * $quantity;
            $pricePerKg = (float) $product->price_per_kg;
            $packagePrice = round(($pricePerKg * $packageWeightGram) / 1000, 2);
            $lineSubtotal = round($packagePrice * $quantity, 2);

            $availableStock = $product->available_stock_gram;
            $demandedTotalForProduct = $productDemands[$product->id] ?? $lineWeightGram;
            $isStockSufficient = $availableStock >= $demandedTotalForProduct;

            if (! $isActive || ! $isStockSufficient) {
                $hasUnavailable = true;
            }

            $subtotal += $lineSubtotal;
            $totalWeightGram += $lineWeightGram;
            $totalQuantity += $quantity;

            $detailedItems->push((object) [
                'product' => $product,
                'weight_option' => $weightOption,
                'quantity' => $quantity,
                'package_weight_gram' => $packageWeightGram,
                'line_weight_gram' => $lineWeightGram,
                'price_per_kg' => $pricePerKg,
                'package_price' => $packagePrice,
                'subtotal' => $lineSubtotal,
                'is_active' => $isActive,
                'is_stock_sufficient' => $isStockSufficient,
                'available_stock_gram' => $availableStock,
            ]);
        }

        return [
            'items' => $detailedItems,
            'subtotal' => $subtotal,
            'total_weight_gram' => $totalWeightGram,
            'total_quantity' => $totalQuantity,
            'has_unavailable' => $hasUnavailable,
        ];
    }
}
