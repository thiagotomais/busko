<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Tenant;
use App\Models\TransportRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransportRouteManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_admin_sees_company_selector_before_routes_list(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@busko.com')->firstOrFail();

        $response = $this->actingAs($admin)
            ->get(route('portal.transport-routes.index'));

        $response->assertOk();
        $response->assertSee('Selecione uma empresa para continuar');
        $response->assertSee('Abrir rotas');
    }

    public function test_global_admin_can_open_routes_list_using_company_uid(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@busko.com')->firstOrFail();
        $tenant = Tenant::where('slug', 'busko-transportes')->firstOrFail();

        $response = $this->actingAs($admin)
            ->get(route('portal.transport-routes.index', ['company' => $tenant->uid]));

        $response->assertOk();
        $response->assertSee('Planejamento de Rotas');
    }

    public function test_company_manager_can_create_transport_route_with_passenger_order(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $driver = Driver::whereHas('user', fn ($query) => $query->where('email', 'thiago@tomais'))->firstOrFail();
        $passenger = Passenger::where('name', 'Pedro Passageiro')->firstOrFail();
        $token = 'transport-route-create-token';

        $createPage = $this->actingAs($manager)
            ->get(route('portal.transport-routes.create'));

        $createPage->assertOk();
        $createPage->assertSee('Nova Rota');
        $createPage->assertSee($passenger->name);

        $response = $this->actingAs($manager)
            ->withSession(['_token' => $token])
            ->post(route('portal.transport-routes.store'), [
                '_token' => $token,
                'name' => 'Rota Escolar Tarde 01',
                'driver_id' => $driver->id,
                'direction' => 'ida',
                'period' => 'tarde',
                'vehicle_name' => 'Van Escolar 01',
                'vehicle_plate' => 'ABC-1234',
                'monday' => '1',
                'tuesday' => '1',
                'wednesday' => '1',
                'thursday' => '1',
                'friday' => '1',
                'passenger_ids' => [$passenger->id],
                'stop_orders' => [
                    $passenger->id => 1,
                ],
                'notes' => 'Rota principal da tarde',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('portal.transport-routes.index'));
        $response->assertSessionHas('success');

        $route = TransportRoute::where('name', 'Rota Escolar Tarde 01')->firstOrFail();

        $this->assertDatabaseHas('transport_routes', [
            'id' => $route->id,
            'tenant_id' => $manager->tenant_id,
            'driver_id' => $driver->id,
            'direction' => 'ida',
            'period' => 'tarde',
            'vehicle_name' => 'Van Escolar 01',
        ]);

        $this->assertDatabaseHas('transport_route_passengers', [
            'transport_route_id' => $route->id,
            'passenger_id' => $passenger->id,
            'stop_order' => 1,
        ]);

        $indexResponse = $this->actingAs($manager)
            ->get(route('portal.transport-routes.index'));

        $indexResponse->assertOk();
        $indexResponse->assertSee('Rota Escolar Tarde 01');
        $indexResponse->assertSee($passenger->name);
    }

    public function test_company_manager_can_edit_transport_route(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $driver = Driver::whereHas('user', fn ($query) => $query->where('email', 'thiago@tomais'))->firstOrFail();
        $passenger = Passenger::where('name', 'Pedro Passageiro')->firstOrFail();

        $transportRoute = TransportRoute::create([
            'tenant_id' => $manager->tenant_id,
            'driver_id' => $driver->id,
            'name' => 'Rota Inicial',
            'direction' => 'ida',
            'period' => 'tarde',
            'vehicle_name' => 'Van Inicial',
            'monday' => true,
            'tuesday' => true,
            'wednesday' => true,
            'thursday' => true,
            'friday' => true,
            'saturday' => false,
            'sunday' => false,
            'is_active' => true,
        ]);

        $transportRoute->passengers()->attach($passenger->id, [
            'tenant_id' => $manager->tenant_id,
            'stop_order' => 1,
        ]);

        $editPage = $this->actingAs($manager)
            ->get(route('portal.transport-routes.edit', $transportRoute));

        $editPage->assertOk();
        $editPage->assertSee('Editar Rota');
        $editPage->assertSee('Rota Inicial');

        $token = 'transport-route-update-token';

        $response = $this->actingAs($manager)
            ->withSession(['_token' => $token])
            ->patch(route('portal.transport-routes.update', $transportRoute), [
                '_token' => $token,
                'name' => 'Rota Atualizada',
                'driver_id' => $driver->id,
                'direction' => 'volta',
                'period' => 'tarde',
                'vehicle_name' => 'Van Atualizada',
                'vehicle_plate' => 'XYZ-9999',
                'monday' => '1',
                'wednesday' => '1',
                'friday' => '1',
                'passenger_ids' => [$passenger->id],
                'stop_orders' => [
                    $passenger->id => 3,
                ],
                'notes' => 'Somente retorno',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('portal.transport-routes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transport_routes', [
            'id' => $transportRoute->id,
            'name' => 'Rota Atualizada',
            'direction' => 'volta',
            'vehicle_name' => 'Van Atualizada',
            'vehicle_plate' => 'XYZ-9999',
            'monday' => true,
            'tuesday' => false,
            'wednesday' => true,
            'thursday' => false,
            'friday' => true,
            'notes' => 'Somente retorno',
        ]);

        $this->assertDatabaseHas('transport_route_passengers', [
            'transport_route_id' => $transportRoute->id,
            'passenger_id' => $passenger->id,
            'stop_order' => 3,
        ]);
    }

    public function test_company_manager_can_view_transport_route_operational_map(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $driver = Driver::whereHas('user', fn ($query) => $query->where('email', 'thiago@tomais'))->firstOrFail();
        $passenger = Passenger::where('name', 'Pedro Passageiro')->firstOrFail();

        $transportRoute = TransportRoute::create([
            'tenant_id' => $manager->tenant_id,
            'driver_id' => $driver->id,
            'name' => 'Rota Detalhada',
            'direction' => 'ida',
            'period' => 'tarde',
            'vehicle_name' => 'Van Mapa',
            'monday' => true,
            'tuesday' => true,
            'wednesday' => true,
            'thursday' => true,
            'friday' => true,
            'is_active' => true,
        ]);

        $transportRoute->passengers()->attach($passenger->id, [
            'tenant_id' => $manager->tenant_id,
            'stop_order' => 1,
        ]);

        $response = $this->actingAs($manager)
            ->get(route('portal.transport-routes.show', $transportRoute));

        $response->assertOk();
        $response->assertSee('Mapa Operacional');
        $response->assertSee('Rota Detalhada');
        $response->assertSee('Casa/Ponto → Escola', false);
        $response->assertSee($passenger->pickup_street);
        $response->assertSee($passenger->school_name);
        $response->assertSee('Sequência de Paradas');
    }

    public function test_cannot_assign_passenger_to_another_route_with_same_direction_and_period(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $driver = Driver::whereHas('user', fn ($query) => $query->where('email', 'thiago@tomais'))->firstOrFail();
        $passenger = Passenger::where('name', 'Pedro Passageiro')->firstOrFail();

        $existingRoute = TransportRoute::create([
            'tenant_id' => $manager->tenant_id,
            'driver_id' => $driver->id,
            'name' => 'Rota Base Ida',
            'direction' => 'ida',
            'period' => 'tarde',
            'monday' => true,
            'tuesday' => true,
            'wednesday' => true,
            'thursday' => true,
            'friday' => true,
            'is_active' => true,
        ]);

        $existingRoute->passengers()->attach($passenger->id, [
            'tenant_id' => $manager->tenant_id,
            'stop_order' => 1,
        ]);

        $token = 'transport-route-duplicate-assignment-token';

        $response = $this->actingAs($manager)
            ->withSession(['_token' => $token])
            ->post(route('portal.transport-routes.store'), [
                '_token' => $token,
                'name' => 'Rota Conflitante Ida',
                'driver_id' => $driver->id,
                'direction' => 'ida',
                'period' => 'tarde',
                'monday' => '1',
                'tuesday' => '1',
                'wednesday' => '1',
                'thursday' => '1',
                'friday' => '1',
                'passenger_ids' => [$passenger->id],
                'stop_orders' => [
                    $passenger->id => 1,
                ],
                'is_active' => '1',
            ]);

        $response->assertSessionHasErrors('passenger_ids');
        $this->assertDatabaseCount('transport_routes', 1);
    }
}