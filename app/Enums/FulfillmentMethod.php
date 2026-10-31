<?php

namespace App\Enums;

enum FulfillmentMethod: string
{
    case Pickup = 'pickup';
    case Delivery = 'delivery';

    public function label(): string
    {
        return match ($this) {
            self::Pickup => 'Ambil di Toko',
            self::Delivery => 'Pengiriman',
        };
    }
}
