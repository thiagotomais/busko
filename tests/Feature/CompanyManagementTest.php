<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_company_listing_page(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@busko.com')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('portal.company.index'));

        $response->assertOk();
        $response->assertSee('Empresas Cadastradas');
        $response->assertSee('Busko Transportes');
    }

    public function test_authenticated_user_can_view_company_management_page(): void
    {
        $this->seed();

        $user = User::where('email', 'thiago@tomais')->firstOrFail();

        $response = $this->actingAs($user)->get(route('portal.company.show'));

        $response->assertOk();
        $response->assertSee('Empresa de Transportes');
        $response->assertSee('Busko Transportes');
    }

    public function test_authenticated_user_can_update_company_details(): void
    {
        $this->seed();

        $user = User::where('email', 'thiago@tomais')->firstOrFail();
        $token = 'company-update-token';

        $response = $this->actingAs($user)
            ->withSession(['_token' => $token])
            ->patch(route('portal.company.update'), [
                '_token' => $token,
                'name' => 'Nova Transportadora Busko',
                'slug' => 'nova-transportadora-busko',
            ]);

        $response->assertRedirect(route('portal.company.show'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tenants', [
            'name' => 'Nova Transportadora Busko',
            'slug' => 'nova-transportadora-busko',
        ]);
    }

    public function test_authenticated_user_can_toggle_company_status(): void
    {
        $this->seed();

        $user = User::where('email', 'thiago@tomais')->firstOrFail();
        $tenant = Tenant::where('slug', 'busko-transportes')->firstOrFail();
        $token = 'company-toggle-token';

        $response = $this->actingAs($user)
            ->withSession(['_token' => $token])
            ->post(route('portal.company.toggle-status'), [
                '_token' => $token,
            ]);

        $response->assertRedirect(route('portal.company.show'));
        $response->assertSessionHas('success');

        $this->assertFalse($tenant->fresh()->is_active);
    }

    public function test_admin_can_update_any_company_from_listing_flow(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@busko.com')->firstOrFail();
        $tenant = Tenant::where('slug', 'busko-transportes')->firstOrFail();
        $token = 'admin-company-edit-token';

        $response = $this->actingAs($admin)
            ->withSession(['_token' => $token])
            ->patch(route('portal.company.admin-update', $tenant), [
                '_token' => $token,
                'name' => 'Busko Transportes Global',
                'slug' => 'busko-transportes-global',
            ]);

        $response->assertRedirect(route('portal.company.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name' => 'Busko Transportes Global',
            'slug' => 'busko-transportes-global',
        ]);
    }
}