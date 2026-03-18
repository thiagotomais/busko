<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_users_index(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@busko.com')->firstOrFail();
        $tenant = Tenant::where('slug', 'busko-transportes')->firstOrFail();

        $response = $this->actingAs($admin)
            ->get(route('portal.users.index', ['company_id' => $tenant->id]));

        $response->assertOk();
        $response->assertSee('Gestão de Usuários');
        $response->assertSee('thiago@tomais');
        $response->assertDontSee('admin@busko.com');
    }

    public function test_authenticated_user_can_update_guardian_profile(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@busko.com')->firstOrFail();
        $guardian = User::where('email', 'guardian@test.com')->firstOrFail();
        $driver = User::where('email', 'thiago@tomais')->firstOrFail()->driver;
        $tenant = Tenant::where('slug', 'busko-transportes')->firstOrFail();
        $token = 'user-edit-token';

        $response = $this->actingAs($admin)
            ->withSession(['_token' => $token])
            ->patch(route('portal.users.update', ['user' => $guardian, 'company_id' => $tenant->id]), [
                '_token' => $token,
                'name' => 'Maria Guardian Editada',
                'email' => 'guardian@test.com',
                'cpf' => '52998224725',
                'primary_driver_id' => $driver->id,
            ]);

        $response->assertRedirect(route('portal.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $guardian->id,
            'name' => 'Maria Guardian Editada',
        ]);

        $this->assertDatabaseHas('guardians', [
            'user_id' => $guardian->id,
            'primary_driver_id' => $driver->id,
            'cpf' => '52998224725',
        ]);
    }

    public function test_authenticated_user_can_toggle_target_user_status(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@busko.com')->firstOrFail();
        $driverUser = User::where('email', 'thiago@tomais')->firstOrFail();
        $tenant = Tenant::where('slug', 'busko-transportes')->firstOrFail();
        $token = 'user-toggle-token';

        $response = $this->actingAs($admin)
            ->withSession(['_token' => $token])
            ->post(route('portal.users.toggle-status', ['user' => $driverUser, 'company_id' => $tenant->id]), [
                '_token' => $token,
            ]);

        $response->assertRedirect(route('portal.users.index'));
        $response->assertSessionHas('success');

        $this->assertFalse($driverUser->fresh()->is_active);
    }
}
