<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryZoneController extends Controller
{
    public function index(): View
    {
        $zones = DeliveryZone::withCount('orders')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.delivery_zones.index', compact('zones'));
    }

    public function create(): View
    {
        return view('admin.delivery_zones.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'fee' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $zone = DeliveryZone::create($validated);

        return redirect()->route('admin.delivery-zones.index')
            ->with('status', "Zona pengiriman '{$zone->name}' berhasil ditambahkan.");
    }

    public function edit(DeliveryZone $deliveryZone): View
    {
        return view('admin.delivery_zones.edit', compact('deliveryZone'));
    }

    public function update(Request $request, DeliveryZone $deliveryZone): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'fee' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);

        $deliveryZone->update($validated);

        return redirect()->route('admin.delivery-zones.index')
            ->with('status', "Zona pengiriman '{$deliveryZone->name}' berhasil diperbarui.");
    }

    public function destroy(DeliveryZone $deliveryZone): RedirectResponse
    {
        if ($deliveryZone->orders()->exists()) {
            return back()->with('error', "Zona '{$deliveryZone->name}' tidak dapat dihapus karena sudah memiliki riwayat pesanan. Anda dapat menonaktifkannya.");
        }

        $name = $deliveryZone->name;
        $deliveryZone->delete();

        return redirect()->route('admin.delivery-zones.index')
            ->with('status', "Zona pengiriman '{$name}' berhasil dihapus.");
    }

    public function toggleActive(DeliveryZone $deliveryZone): RedirectResponse
    {
        $deliveryZone->is_active = ! $deliveryZone->is_active;
        $deliveryZone->save();

        $state = $deliveryZone->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('status', "Zona pengiriman '{$deliveryZone->name}' berhasil {$state}.");
    }
}
