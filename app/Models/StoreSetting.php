<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'business_name',
    'logo_path',
    'business_description',
    'phone',
    'email',
    'address',
    'opening_hours',
    'standard_beef_price_per_kg',
    'payment_expiry_minutes',
    'timezone',
])]
class StoreSetting extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'standard_beef_price_per_kg' => 'decimal:2',
            'payment_expiry_minutes' => 'integer',
        ];
    }

    public static function current(): self
    {
        return once(function () {
            return static::firstOrCreate([], [
                'business_name' => 'DONS MEAT',
                'standard_beef_price_per_kg' => 140000.00,
                'payment_expiry_minutes' => 60,
                'timezone' => 'Asia/Makassar',
                'business_description' => 'Pusat penyedia aneka daging segar berkualitas, higienis, dan terpercaya.',
                'phone' => '08123456789',
                'address' => 'Jl. Pengayoman, Makassar',
                'opening_hours' => "Senin - Sabtu: 08.00 - 18.00\nMinggu: 08.00 - 15.00",
            ]);
        });
    }
}
