<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Models\Guardian;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedUserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_driver_from_unified_form(): void
    {
        $this->seed();

        $authUser = User::where('email', 'admin@busko.com')->firstOrFail();
        $tenantId = Tenant::where('slug', 'busko-transportes')->firstOrFail()->id;
        $token = 'unified-driver-token';

        $response = $this->actingAs($authUser)
            ->withSession(['_token' => $token])
            ->post(route('portal.users.store', ['company_id' => $tenantId]), [
                '_token' => $token,
                'type' => 'driver',
                'name' => 'Driver Unificado',
                'email' => 'driver.unificado@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'cpf' => '98765432100',
                'cnh' => '98765012345',
            ]);

        $response->assertRedirect(route('portal.dashboard'));
        $response->assertSessionHas('success');

        $user = User::where('email', 'driver.unificado@example.com')->firstOrFail();
        $this->assertSame(UserType::DRIVER, $user->type);
        $this->assertSame($tenantId, $user->tenant_id);

        $this->assertDatabaseHas('drivers', [
            'user_id' => $user->id,
            'tenant_id' => $tenantId,
            'cpf' => '98765432100',
            'cnh' => '98765012345',
        ]);
    }

    public function test_can_register_guardian_with_primary_driver_from_unified_form(): void
    {
        $this->seed();

        $authUser = User::where('email', 'admin@busko.com')->firstOrFail();
        $driver = User::where('email', 'thiago@tomais')->firstOrFail()->driver;
        $tenantId = Tenant::where('slug', 'busko-transportes')->firstOrFail()->id;
        $token = 'unified-guardian-token';

        $response = $this->actingAs($authUser)
            ->withSession(['_token' => $token])
            ->post(route('portal.users.store', ['company_id' => $tenantId]), [
                '_token' => $token,
                'type' => 'guardian',
                'name' => 'Guardian Unificado',
                'email' => 'guardian.unificado@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'cpf' => '52998224725',
                'primary_driver_id' => $driver->id,
            ]);

        $response->assertRedirect(route('portal.dashboard'));
        $response->assertSessionHas('success');

        $guardianUser = User::where('email', 'guardian.unificado@example.com')->firstOrFail();
        $this->assertSame(UserType::GUARDIAN, $guardianUser->type);
        $this->assertSame($tenantId, $guardianUser->tenant_id);

        $guardian = Guardian::where('user_id', $guardianUser->id)->firstOrFail();
        $this->assertSame($driver->id, $guardian->primary_driver_id);

        $this->assertDatabaseHas('driver_guardians', [
            'driver_id' => $driver->id,
            'guardian_id' => $guardian->id,
        ]);
    }

    public function test_can_register_admin_from_unified_form(): void
    {
        $this->seed();

        $authUser = User::where('email', 'admin@busko.com')->firstOrFail();
        $tenantId = Tenant::where('slug', 'busko-transportes')->firstOrFail()->id;
        $token = 'unified-admin-token';

        $response = $this->actingAs($authUser)
            ->withSession(['_token' => $token])
            ->post(route('portal.users.store', ['company_id' => $tenantId]), [
                '_token' => $token,
                'type' => 'admin',
                'name' => 'Admin Unificado',
                'email' => 'admin.unificado@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('portal.dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'tenant_id' => null,
            'name' => 'Admin Unificado',
            'email' => 'admin.unificado@example.com',
            'type' => UserType::ADMIN->value,
        ]);
    }
}