<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'name' => 'Makassar Kota - Zona 1 (Panakkukang, Rappocini, Makassar)',
                'description' => 'Area pusat perkantoran & perumahan kota Makassar. Estimasi tiba 1-2 jam setelah siap.',
                'fee' => 15000.00,
                'minimum_order_amount' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Makassar Barat & Selatan - Zona 2 (Mariso, Mamajang, Tamalate)',
                'description' => 'Area pesisir Losari, CPI, dan Tanjung Bunga. Estimasi tiba 1-3 jam.',
                'fee' => 20000.00,
                'minimum_order_amount' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Makassar Timur & Utara - Zona 3 (Tamalanrea, Biringkanaya, Manggala)',
                'description' => 'Area perintis kemerdekaan, kampus Unhas, Antang, dan daya.',
                'fee' => 25000.00,
                'minimum_order_amount' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Perbatasan Gowa & Maros (Sungguminasa & Mandai)',
                'description' => 'Wilayah perbatasan lingkar luar kota Makassar.',
                'fee' => 35000.00,
                'minimum_order_amount' => 100000.00,
                'is_active' => true,
            ],
        ];

        foreach ($zones as $zone) {
            DeliveryZone::updateOrCreate(
                ['name' => $zone['name']],
                $zone
            );
        }
    }
}
