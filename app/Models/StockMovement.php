<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'product_id',
    'order_id',
    'created_by_user_id',
    'movement_type',
    'stock_delta_gram',
    'reserved_delta_gram',
    'stock_balance_after_gram',
    'reserved_balance_after_gram',
    'reason',
    'created_at',
])]
class StockMovement extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'movement_type' => StockMovementType::class,
            'stock_delta_gram' => 'integer',
            'reserved_delta_gram' => 'integer',
            'stock_balance_after_gram' => 'integer',
            'reserved_balance_after_gram' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
