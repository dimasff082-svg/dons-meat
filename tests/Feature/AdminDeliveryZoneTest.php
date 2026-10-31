<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDeliveryZoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_delivery_zones(): void
    {
        $response = $this->get('/admin/delivery-zones');
        $response->assertRedirect('/admin/login');

        $responsePost = $this->post('/admin/delivery-zones', []);
        $responsePost->assertRedirect('/admin/login');
    }

    public function test_admin_and_owner_can_view_delivery_zones(): void
    {
        $admin = User::factory()->admin()->create();
        DeliveryZone::factory()->create([
            'name' => 'Zona Rappocini',
            'fee' => 15000.00,
        ]);

        $response = $this->actingAs($admin)->get('/admin/delivery-zones');

        $response->assertStatus(200);
        $response->assertSee('Zona Rappocini');
        $response->assertSee('15.000');
    }

    public function test_admin_can_create_delivery_zone(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/delivery-zones', [
            'name' => 'Zona Tamalanrea & Biringkanaya',
            'fee' => 25000.00,
            'description' => 'Area kampus Unhas dan Daya',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/delivery-zones');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('delivery_zones', [
            'name' => 'Zona Tamalanrea & Biringkanaya',
            'fee' => 25000.00,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_delivery_zone(): void
    {
        $admin = User::factory()->admin()->create();
        $zone = DeliveryZone::factory()->create([
            'name' => 'Zona Lama',
            'fee' => 10000.00,
        ]);

        $response = $this->actingAs($admin)->put("/admin/delivery-zones/{$zone->id}", [
            'name' => 'Zona Baru Diupdate',
            'fee' => 15000.00,
            'description' => 'Deskripsi update',
            'is_active' => 1,
        ]);

        $response->assertRedirect('/admin/delivery-zones');

        $zone->refresh();
        $this->assertEquals('Zona Baru Diupdate', $zone->name);
        $this->assertEquals(15000.00, (float) $zone->fee);
    }

    public function test_admin_can_toggle_delivery_zone_active_status(): void
    {
        $admin = User::factory()->admin()->create();
        $zone = DeliveryZone::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->patch("/admin/delivery-zones/{$zone->id}/toggle-active");

        $response->assertRedirect();
        $zone->refresh();
        $this->assertFalse($zone->is_active);

        // Toggle again
        $this->actingAs($admin)->patch("/admin/delivery-zones/{$zone->id}/toggle-active");
        $zone->refresh();
        $this->assertTrue($zone->is_active);
    }

    public function test_admin_cannot_delete_zone_with_existing_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $zone = DeliveryZone::factory()->create();
        Order::factory()->create(['delivery_zone_id' => $zone->id]);

        $response = $this->actingAs($admin)->delete("/admin/delivery-zones/{$zone->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('delivery_zones', ['id' => $zone->id]);
    }
}
