<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'category_id',
    'sku',
    'name',
    'slug',
    'description',
    'image_path',
    'price_per_kg',
    'stock_gram',
    'reserved_stock_gram',
    'low_stock_threshold_gram',
    'is_active',
    'is_featured',
])]
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'price_per_kg' => 'decimal:2',
            'stock_gram' => 'integer',
            'reserved_stock_gram' => 'integer',
            'low_stock_threshold_gram' => 'integer',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function weightOptions(): HasMany
    {
        return $this->hasMany(ProductWeightOption::class)->orderBy('sort_order')->orderBy('weight_gram');
    }

    public function activeWeightOptions(): HasMany
    {
        return $this->hasMany(ProductWeightOption::class)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('weight_gram');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest('id');
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(ProductPriceHistory::class)->latest('id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getAvailableStockGramAttribute(): int
    {
        return (int) max(0, $this->stock_gram - $this->reserved_stock_gram);
    }

    public function isLowStock(): bool
    {
        return $this->available_stock_gram <= $this->low_stock_threshold_gram;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
