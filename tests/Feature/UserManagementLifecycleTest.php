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
                'type' => 'guardian',
                'name' => 'Maria Guardian Editada',
                'email' => 'guardian@test.com',
                'cpf' => '52998224725',
                'primary_driver_id' => $driver->id,
            ]);

        $response->assertRedirect(route('portal.users.index', ['company_id' => $tenant->id]));
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

        $response->assertRedirect(route('portal.users.index', ['company_id' => $tenant->id]));
        $response->assertSessionHas('success');

        $this->assertFalse($driverUser->fresh()->is_active);
    }

    public function test_admin_can_change_user_type_from_driver_to_guardian(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@busko.com')->firstOrFail();
        $driverUser = User::where('email', 'thiago@tomais')->firstOrFail();
        $tenant = Tenant::where('slug', 'busko-transportes')->firstOrFail();
        $token = 'change-type-token';

        $response = $this->actingAs($admin)
            ->withSession(['_token' => $token])
            ->patch(route('portal.users.update', ['user' => $driverUser, 'company_id' => $tenant->id]), [
                '_token' => $token,
                'type' => 'guardian',
                'name' => 'Thiago Guardian',
                'email' => 'thiago@tomais',
                'cpf' => '52998224725',
            ]);

        $response->assertRedirect(route('portal.users.index', ['company_id' => $tenant->id]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $driverUser->id,
            'type' => 'guardian',
            'is_company_manager' => false,
        ]);

        $this->assertDatabaseMissing('drivers', ['user_id' => $driverUser->id]);
        $this->assertDatabaseHas('guardians', ['user_id' => $driverUser->id]);
    }

    public function test_company_manager_can_create_driver_but_cannot_create_admin(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $this->assertTrue((bool) $manager->is_company_manager);
        $token = 'manager-create-token';

        $createDriver = $this->actingAs($manager)
            ->withSession(['_token' => $token])
            ->post(route('portal.users.store'), [
                '_token' => $token,
                'type' => 'driver',
                'name' => 'Motorista da Empresa',
                'email' => 'empresa.driver@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'cpf' => '39053344705',
                'cnh' => '12345678901',
                'is_company_manager' => '1',
            ]);

        $createDriver->assertRedirect(route('portal.dashboard'));
        $createDriver->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'empresa.driver@example.com',
            'type' => 'driver',
            'is_company_manager' => true,
        ]);

        $forbidAdmin = $this->actingAs($manager)
            ->withSession(['_token' => $token])
            ->post(route('portal.users.store'), [
                '_token' => $token,
                'type' => 'admin',
                'name' => 'Admin Indevido',
                'email' => 'admin.indevido@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $forbidAdmin->assertSessionHasErrors('type');
        $this->assertDatabaseMissing('users', ['email' => 'admin.indevido@example.com']);
    }
}
