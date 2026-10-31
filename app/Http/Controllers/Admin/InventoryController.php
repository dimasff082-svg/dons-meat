<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StockMovementType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'weightOptions'])
            ->orderBy('category_id')
            ->orderBy('name');

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

        if ($request->query('filter') === 'low_stock') {
            $query->whereRaw('(stock_gram - reserved_stock_gram) <= low_stock_threshold_gram');
        } elseif ($request->query('filter') === 'out_of_stock') {
            $query->whereRaw('(stock_gram - reserved_stock_gram) <= 0');
        }

        $products = $query->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get();

        $totalPhysicalWeightGram = (int) Product::sum('stock_gram');
        $totalReservedWeightGram = (int) Product::sum('reserved_stock_gram');
        $totalAvailableWeightGram = max(0, $totalPhysicalWeightGram - $totalReservedWeightGram);
        $lowStockCount = Product::whereRaw('(stock_gram - reserved_stock_gram) <= low_stock_threshold_gram')->count();

        return view('admin.inventory.index', compact(
            'products',
            'categories',
            'totalPhysicalWeightGram',
            'totalReservedWeightGram',
            'totalAvailableWeightGram',
            'lowStockCount'
        ));
    }

    public function movements(Request $request): View
    {
        $query = StockMovement::with(['product.category', 'createdByUser', 'order'])
            ->latest('id');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->string('movement_type'));
        }

        $movements = $query->paginate(25)->withQueryString();
        $products = Product::orderBy('name')->get();

        return view('admin.inventory.movements', compact('movements', 'products'));
    }

    public function createAdjustment(Request $request): View
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $selectedProductId = $request->integer('product_id');

        return view('admin.inventory.adjust', compact('products', 'selectedProductId'));
    }

    public function storeAdjustment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'movement_type' => [
                'required',
                Rule::in([
                    StockMovementType::Inbound->value,
                    StockMovementType::AdjustmentIn->value,
                    StockMovementType::AdjustmentOut->value,
                    StockMovementType::Damaged->value,
                ]),
            ],
            'weight_gram' => ['required', 'integer', 'min:1', 'max:5000000'],
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ]);

        $movementType = StockMovementType::from($validated['movement_type']);
        $weightGram = (int) $validated['weight_gram'];
        $reason = $validated['reason'];

        $errorMsg = DB::transaction(function () use ($validated, $movementType, $weightGram, $reason) {
            $product = Product::where('id', $validated['product_id'])->lockForUpdate()->first();

            $isAddition = in_array($movementType, [
                StockMovementType::Inbound,
                StockMovementType::AdjustmentIn,
            ], true);

            $stockDelta = $isAddition ? $weightGram : -$weightGram;
            $newStock = $product->stock_gram + $stockDelta;

            // Cannot reduce physical stock below currently reserved stock for pending orders
            if ($newStock < $product->reserved_stock_gram) {
                return "Penyesuaian ditolak: Stok fisik tidak boleh kurang dari stok yang sedang dicadangkan untuk pesanan online ({$product->reserved_stock_gram} g). Maksimal pengurangan: ".($product->stock_gram - $product->reserved_stock_gram).' g.';
            }

            $product->stock_gram = $newStock;
            $product->save();

            StockMovement::create([
                'product_id' => $product->id,
                'order_id' => null,
                'created_by_user_id' => auth()->id(),
                'movement_type' => $movementType,
                'stock_delta_gram' => $stockDelta,
                'reserved_delta_gram' => 0,
                'stock_balance_after_gram' => $product->stock_gram,
                'reserved_balance_after_gram' => $product->reserved_stock_gram,
                'reason' => $reason,
                'created_at' => now(),
            ]);

            return null;
        });

        if ($errorMsg) {
            return back()->withInput()->withErrors(['weight_gram' => $errorMsg]);
        }

        return redirect()->route('admin.stock.index')
            ->with('status', 'Penyesuaian stok berhasil disimpan dan dicatat dalam riwayat mutasi.');
    }
}
