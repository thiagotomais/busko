<?php

namespace Tests\Feature;

use App\Models\Guardian;
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
}
