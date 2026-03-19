<?php

namespace Tests\Feature;

use App\Models\Guardian;
use App\Models\Passenger;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PassengerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_manager_can_create_passenger_linked_to_guardian(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $guardian = Guardian::whereHas('user', fn ($query) => $query->where('email', 'guardian@test.com'))->firstOrFail();
        $token = 'passenger-create-token';

        $response = $this->actingAs($manager)
            ->withSession(['_token' => $token])
            ->post(route('portal.passengers.store'), [
                '_token' => $token,
                'guardian_id' => $guardian->id,
                'service_type' => 'ida_volta',
                'name' => 'Aluno Teste',
                'birth_date' => '2015-04-12',
                'school_grade' => '5º ano',
                'period' => 'tarde',
                'rg' => '123456789',
                'residential_zip' => '01311-200',
                'residential_street' => 'Avenida Paulista',
                'residential_number' => '1000',
                'residential_complement' => 'Apto 12',
                'residential_neighborhood' => 'Bela Vista',
                'residential_city' => 'São Paulo',
                'residential_state' => 'SP',
                'pickup_zip' => '01311200',
                'pickup_street' => 'Rua das Crianças',
                'pickup_number' => '45',
                'pickup_neighborhood' => 'Centro',
                'pickup_city' => 'São Paulo',
                'pickup_state' => 'SP',
                'dropoff_zip' => '01310100',
                'dropoff_street' => 'Rua do Saber',
                'dropoff_number' => '100',
                'dropoff_neighborhood' => 'Vila Mariana',
                'dropoff_city' => 'São Paulo',
                'dropoff_state' => 'SP',
                'school_name' => 'Colégio Exemplo',
                'school_zip' => '01310100',
                'school_street' => 'Rua do Saber',
                'school_number' => '100',
                'school_neighborhood' => 'Vila Mariana',
                'school_city' => 'São Paulo',
                'school_state' => 'SP',
                'entry_time' => '13:00',
                'exit_time' => '18:00',
            ]);

        $response->assertRedirect(route('portal.passengers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('passengers', [
            'guardian_id' => $guardian->id,
            'name' => 'Aluno Teste',
            'service_type' => 'ida_volta',
            'period' => 'tarde',
            'residential_zip' => '01311200',
            'pickup_state' => 'SP',
            'dropoff_state' => 'SP',
            'school_name' => 'Colégio Exemplo',
        ]);
    }

    public function test_authenticated_user_can_lookup_cep(): void
    {
        $this->seed();

        $user = User::where('email', 'thiago@tomais')->firstOrFail();

        Http::fake([
            'https://viacep.com.br/ws/01311200/json/*' => Http::response([
                'cep' => '01311-200',
                'logradouro' => 'Avenida Paulista',
                'bairro' => 'Bela Vista',
                'localidade' => 'São Paulo',
                'uf' => 'SP',
            ], 200),
        ]);

        $response = $this->actingAs($user)
            ->get(route('portal.api.cep.lookup', ['cep' => '01311-200']));

        $response->assertOk();
        $response->assertJson([
            'zip' => '01311200',
            'street' => 'Avenida Paulista',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
        ]);
    }

    public function test_passenger_create_form_requires_guardian_context_and_shows_read_only_guardian(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $guardian = Guardian::whereHas('user', fn ($query) => $query->where('email', 'guardian@test.com'))->firstOrFail();

        $this->actingAs($manager)
            ->get(route('portal.passengers.create'))
            ->assertNotFound();

        $response = $this->actingAs($manager)
            ->get(route('portal.passengers.create', ['guardian_id' => $guardian->id]));

        $response->assertOk();
        $response->assertSee($guardian->user->name);
        $response->assertSee($guardian->cpf);
        $response->assertDontSee('Selecione...');
        $response->assertSee('type="hidden" id="guardian_id" name="guardian_id"', false);
    }

    public function test_passengers_index_does_not_expose_generic_create_action(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();

        $response = $this->actingAs($manager)
            ->get(route('portal.passengers.index'));

        $response->assertOk();
        $response->assertDontSee('+ Novo Passageiro');
        $response->assertDontSee('Cadastrar Primeiro Passageiro');
        $response->assertSee('devem ser cadastrados a partir da tela de guardiões');
        $response->assertSee('Visualizar Detalhes');
        $response->assertSee('Editar');
    }

    public function test_guardian_show_displays_passenger_listing(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $guardian = Guardian::whereHas('user', fn ($query) => $query->where('email', 'guardian@test.com'))->firstOrFail();

        $response = $this->actingAs($manager)
            ->get(route('portal.guardians.show', $guardian));

        $response->assertOk();
        $response->assertSee('Pedro Passageiro');
        $response->assertSee('Editar Passageiro');
    }

    public function test_company_manager_can_edit_passenger_from_list(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $passenger = Passenger::where('name', 'Pedro Passageiro')->firstOrFail();
        $token = 'passenger-update-token';

        $editResponse = $this->actingAs($manager)
            ->get(route('portal.passengers.edit', $passenger));

        $editResponse->assertOk();
        $editResponse->assertSee('Editar Passageiro');
        $editResponse->assertSee($passenger->name);

        $response = $this->actingAs($manager)
            ->withSession(['_token' => $token])
            ->patch(route('portal.passengers.update', $passenger), [
                '_token' => $token,
                'guardian_id' => $passenger->guardian_id,
                'service_type' => 'volta',
                'name' => 'Pedro Passageiro Atualizado',
                'birth_date' => '2015-03-10',
                'school_grade' => '6º ano',
                'period' => 'manha',
                'rg' => '999999999',
                'residential_zip' => '01311-200',
                'residential_street' => 'Avenida Paulista',
                'residential_number' => '2000',
                'residential_complement' => 'Apto 21',
                'residential_neighborhood' => 'Bela Vista',
                'residential_city' => 'São Paulo',
                'residential_state' => 'SP',
                'pickup_zip' => '01311200',
                'pickup_street' => 'Rua Atualizada',
                'pickup_number' => '50',
                'pickup_complement' => 'Portão 2',
                'pickup_neighborhood' => 'Centro',
                'pickup_city' => 'São Paulo',
                'pickup_state' => 'SP',
                'dropoff_zip' => '01310100',
                'dropoff_street' => 'Rua da Escola',
                'dropoff_number' => '150',
                'dropoff_complement' => 'Bloco B',
                'dropoff_neighborhood' => 'Vila Mariana',
                'dropoff_city' => 'São Paulo',
                'dropoff_state' => 'SP',
                'school_name' => 'Escola Atualizada',
                'school_zip' => '01310100',
                'school_street' => 'Rua da Escola',
                'school_number' => '150',
                'school_complement' => 'Bloco B',
                'school_neighborhood' => 'Vila Mariana',
                'school_city' => 'São Paulo',
                'school_state' => 'SP',
                'entry_time' => '07:00',
                'exit_time' => '12:00',
            ]);

        $response->assertRedirect(route('portal.passengers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('passengers', [
            'id' => $passenger->id,
            'guardian_id' => $passenger->guardian_id,
            'name' => 'Pedro Passageiro Atualizado',
            'service_type' => 'volta',
            'school_grade' => '6º ano',
            'pickup_street' => 'Rua Atualizada',
            'school_name' => 'Escola Atualizada',
            'entry_time' => '07:00',
            'exit_time' => '12:00',
        ]);
    }
}
