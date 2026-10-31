<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreSettingController extends Controller
{
    public function edit(): View
    {
        $setting = StoreSetting::current();

        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = StoreSetting::current();

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:150'],
            'standard_beef_price_per_kg' => ['required', 'numeric', 'min:0'],
            'business_description' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string'],
            'opening_hours' => ['nullable', 'string'],
            'payment_expiry_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'timezone' => ['required', 'string', 'max:50'],
        ]);

        $setting->update($validated);

        return redirect()->route('admin.settings.edit')
            ->with('status', 'Pengaturan usaha dan harga standar daging sapi berhasil diperbarui.');
    }
}
