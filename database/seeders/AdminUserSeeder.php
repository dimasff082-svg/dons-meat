<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds for local development.
     */
    public function run(): void
    {
        // 1. Initial Store Settings
        StoreSetting::firstOrCreate(
            ['id' => 1],
            [
                'business_name' => 'DONS MEAT',
                'standard_beef_price_per_kg' => 140000.00,
                'payment_expiry_minutes' => 60,
                'timezone' => 'Asia/Makassar',
                'business_description' => 'Pusat daging sapi segar dan berkualitas tinggi untuk kebutuhan rumah tangga dan kuliner.',
                'phone' => '081234567890',
                'email' => 'kontak@donsmeat.local',
                'address' => 'Jl. Pengayoman No. 12, Panakkukang, Makassar',
                'opening_hours' => "Senin - Sabtu: 08:00 - 18:00 WITA\nMinggu: 08:00 - 15:00 WITA",
            ]
        );

        // 2. Demo Owner Accounts (Local Dev Only)
        User::firstOrCreate(
            ['email' => 'owner@donsmeat.local'],
            [
                'name' => 'Pemilik DONS MEAT',
                'password' => Hash::make('password'),
                'role' => UserRole::Owner,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'pemilik@donsmeat.local'],
            [
                'name' => 'Pemilik DONS MEAT',
                'password' => Hash::make('password'),
                'role' => UserRole::Owner,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. Demo Admin Account (Local Dev Only)
        User::firstOrCreate(
            ['email' => 'admin@donsmeat.local'],
            [
                'name' => 'Admin Operasional',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 4. Demo Cashier Accounts (Local Dev Only)
        User::firstOrCreate(
            ['email' => 'kasir@donsmeat.local'],
            [
                'name' => 'Kasir Toko',
                'password' => Hash::make('password'),
                'role' => UserRole::Cashier,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'cashier@donsmeat.local'],
            [
                'name' => 'Kasir Toko',
                'password' => Hash::make('password'),
                'role' => UserRole::Cashier,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
