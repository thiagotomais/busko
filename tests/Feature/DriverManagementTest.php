<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_driver_create_page(): void
    {
        $this->seed();

        $user = User::where('email', 'thiago@tomais')->firstOrFail();

        $response = $this->actingAs($user)->get(route('portal.drivers.create'));

        $response->assertOk();
        $response->assertSee('Cadastrar Motorista');
    }

    public function test_authenticated_user_can_register_driver_for_current_company(): void
    {
        $this->seed();

        $authUser = User::where('email', 'thiago@tomais')->firstOrFail();
        $authTenantId = $authUser->driver->tenant_id;
        $token = 'driver-create-token';

        $response = $this->actingAs($authUser)
            ->withSession(['_token' => $token])
            ->post(route('portal.drivers.store'), [
                '_token' => $token,
                'name' => 'Motorista Novo',
                'email' => 'motorista.novo@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'cpf' => '12345678909',
                'cnh' => '11223344556',
            ]);

        $response->assertRedirect(route('portal.drivers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'motorista.novo@example.com',
            'type' => UserType::DRIVER->value,
        ]);

        $newUser = User::where('email', 'motorista.novo@example.com')->firstOrFail();

        $this->assertDatabaseHas('drivers', [
            'user_id' => $newUser->id,
            'tenant_id' => $authTenantId,
            'cpf' => '12345678909',
            'cnh' => '11223344556',
        ]);

        $this->assertNotNull(Driver::where('user_id', $newUser->id)->firstOrFail()->slug);
    }
}
