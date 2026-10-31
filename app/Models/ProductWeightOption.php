<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'weight_gram', 'label', 'sort_order', 'is_active'])]
class ProductWeightOption extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'weight_gram' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function calculatePrice(): float
    {
        if (! $this->relationLoaded('product')) {
            $this->load('product');
        }

        $pricePerKg = (float) ($this->product->price_per_kg ?? 0);

        return round(($pricePerKg * $this->weight_gram) / 1000, 2);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
