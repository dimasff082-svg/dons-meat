<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Inbound = 'inbound';
    case Sale = 'sale';
    case AdjustmentIn = 'adjustment_in';
    case AdjustmentOut = 'adjustment_out';
    case Damaged = 'damaged';
    case Reserve = 'reserve';
    case ReleaseReservation = 'release_reservation';

    public function label(): string
    {
        return match ($this) {
            self::Inbound => 'Stok Masuk',
            self::Sale => 'Penjualan',
            self::AdjustmentIn => 'Penyesuaian Masuk',
            self::AdjustmentOut => 'Penyesuaian Keluar',
            self::Damaged => 'Rusak/Afkir',
            self::Reserve => 'Reservasi Pesanan',
            self::ReleaseReservation => 'Pelepasan Reservasi',
        };
    }
}
