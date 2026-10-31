<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'product_id',
    'product_name_snapshot',
    'product_sku_snapshot',
    'weight_per_package_gram',
    'quantity',
    'total_weight_gram',
    'price_per_kg_snapshot',
    'subtotal',
])]
class OrderItem extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'weight_per_package_gram' => 'integer',
            'quantity' => 'integer',
            'total_weight_gram' => 'integer',
            'price_per_kg_snapshot' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
