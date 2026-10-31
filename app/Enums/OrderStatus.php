<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Processing = 'processing';
    case ReadyForPickup = 'ready_for_pickup';
    case OnDelivery = 'on_delivery';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Menunggu Pembayaran',
            self::Processing => 'Diproses',
            self::ReadyForPickup => 'Siap Diambil',
            self::OnDelivery => 'Dalam Pengiriman',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::Expired => 'Kedaluwarsa',
        };
    }
}
