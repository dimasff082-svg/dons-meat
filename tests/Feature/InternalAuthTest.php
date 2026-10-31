<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('DONS MEAT');
        $response->assertSee('Login Internal');
    }

    public function test_authenticated_user_is_redirected_away_from_login_page(): void
    {
        $owner = User::factory()->owner()->create();
        $responseOwner = $this->actingAs($owner)->get('/admin/login');
        $responseOwner->assertRedirect(route('admin.reports.sales'));

        $cashier = User::factory()->cashier()->create();
        $responseCashier = $this->actingAs($cashier)->get('/admin/login');
        $responseCashier->assertRedirect(route('admin.direct-sales.create'));

        $admin = User::factory()->admin()->create();
        $responseAdmin = $this->actingAs($admin)->get('/admin/login');
        $responseAdmin->assertRedirect(route('admin.dashboard'));
    }

    public function test_owner_can_login_with_valid_credentials(): void
    {
        $owner = User::factory()->owner()->create([
            'email' => 'owner@donsmeat.local',
            'password' => 'secret123',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'owner@donsmeat.local',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.reports.sales'));
        $this->assertAuthenticatedAs($owner);
    }

    public function test_cashier_can_login_with_valid_credentials(): void
    {
        $cashier = User::factory()->cashier()->create([
            'email' => 'kasir@donsmeat.local',
            'password' => 'secret123',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'kasir@donsmeat.local',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.direct-sales.create'));
        $this->assertAuthenticatedAs($cashier);
    }

    public function test_can_login_using_role_alias_shorthand(): void
    {
        $cashier = User::factory()->cashier()->create([
            'email' => 'kasir@donsmeat.local',
            'password' => 'secret123',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'kasir',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.direct-sales.create'));
        $this->assertAuthenticatedAs($cashier);
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@donsmeat.local',
            'password' => 'secret123',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@donsmeat.local',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@donsmeat.local',
            'password' => 'secret123',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@donsmeat.local',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->inactive()->create([
            'email' => 'inactive@donsmeat.local',
            'password' => 'secret123',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'inactive@donsmeat.local',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('admin.login'));
    }

    public function test_inactive_authenticated_user_is_logged_out_by_middleware(): void
    {
        $inactiveUser = User::factory()->inactive()->create();

        $response = $this->actingAs($inactiveUser)->get('/admin');

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_admin_can_access_users_management_and_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $responseUsers = $this->actingAs($admin)->get('/admin/users');
        $responseUsers->assertStatus(200);
        $responseUsers->assertSee('Daftar Akun Internal');

        $responseSettings = $this->actingAs($admin)->get('/admin/settings');
        $responseSettings->assertStatus(200);
        $responseSettings->assertSee('Pengaturan Usaha');
    }

    public function test_owner_cannot_access_settings_and_user_management(): void
    {
        $owner = User::factory()->owner()->create();

        $responseUsers = $this->actingAs($owner)->get('/admin/users');
        $responseUsers->assertStatus(403);

        $responseSettings = $this->actingAs($owner)->get('/admin/settings');
        $responseSettings->assertStatus(403);
    }

    public function test_owner_can_access_sales_and_low_stock_reports(): void
    {
        $owner = User::factory()->owner()->create();

        $responseSales = $this->actingAs($owner)->get('/admin/reports/sales');
        $responseSales->assertStatus(200);

        $responseLowStock = $this->actingAs($owner)->get('/admin/reports/low-stock');
        $responseLowStock->assertStatus(200);
    }

    public function test_cashier_can_access_direct_sales_and_orders_only(): void
    {
        $cashier = User::factory()->cashier()->create();

        // Accessible by cashier
        $responseDirectSales = $this->actingAs($cashier)->get('/admin/direct-sales');
        $responseDirectSales->assertStatus(200);

        $responseOrders = $this->actingAs($cashier)->get('/admin/orders');
        $responseOrders->assertStatus(200);

        // Forbidden for cashier
        $responseUsers = $this->actingAs($cashier)->get('/admin/users');
        $responseUsers->assertStatus(403);

        $responseReports = $this->actingAs($cashier)->get('/admin/reports/sales');
        $responseReports->assertStatus(403);

        $responseSettings = $this->actingAs($cashier)->get('/admin/settings');
        $responseSettings->assertStatus(403);
    }

    public function test_cashier_is_redirected_to_direct_sales_when_accessing_dashboard(): void
    {
        $cashier = User::factory()->cashier()->create();

        $response = $this->actingAs($cashier)->get('/admin');
        $response->assertRedirect(route('admin.direct-sales.create'));
    }

    public function test_owner_is_redirected_to_sales_report_when_accessing_dashboard(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->get('/admin');
        $response->assertRedirect(route('admin.reports.sales'));
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->owner()->create();

        $response = $this->actingAs($user)->post('/admin/logout');

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }
}
