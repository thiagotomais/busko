<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Driver;
use App\Models\DriverGuardian;
use App\Models\Guardian;
use App\Models\Passenger;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a tenant
        $tenant = Tenant::create([
            'uid' => Str::uuid()->toString(),
            'name' => 'Busko Transportes',
            'slug' => 'busko-transportes',
            'is_active' => true,
            'bank_code' => '001',
            'bank_branch' => '0001',
            'bank_account' => '123456-7',
            'bank_account_type' => 'corrente',
            'pix_key' => 'empresa@busko.com.br',
        ]);

        // Create addresses
        $driverAddress = Address::create([
            'tenant_id' => $tenant->id,
            'street' => 'Rua das Flores',
            'number' => '123',
            'city' => 'São Paulo',
            'state' => 'sp',
            'zip' => '01234-567',
        ]);

        $guardianAddress = Address::create([
            'tenant_id' => $tenant->id,
            'street' => 'Avenida Paulista',
            'number' => '456',
            'city' => 'São Paulo',
            'state' => 'sp',
            'zip' => '01311-200',
        ]);

        // Create driver user and profile
        $driverUser = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Thiago Driver',
            'email' => 'thiago@tomais',
            'password' => Hash::make('thiago@tomais'),
            'type' => 'driver',
            'is_active' => true,
            'is_company_manager' => true,
        ]);

        $driver = Driver::create([
            'tenant_id' => $tenant->id,
            'user_id' => $driverUser->id,
            'cpf' => '123.456.789-00',
            'cnh' => '12345678900',
            'address_id' => $driverAddress->id,
            'slug' => Str::uuid()->toString(),
        ]);

        // Create guardian user and profile
        $guardianUser = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Maria Guardian',
            'email' => 'guardian@test.com',
            'password' => Hash::make('password'),
            'type' => 'guardian',
            'is_active' => true,
        ]);

        $guardian = Guardian::create([
            'tenant_id' => $tenant->id,
            'user_id' => $guardianUser->id,
            'primary_driver_id' => $driver->id,
            'cpf' => '987.654.321-00',
            'address_id' => $guardianAddress->id,
            'slug' => Str::uuid()->toString(),
        ]);

        // Create global admin user (not linked to a specific tenant)
        User::create([
            'name' => 'Admin Busko',
            'email' => 'admin@busko.com',
            'password' => Hash::make('admin@busko'),
            'type' => 'admin',
            'is_active' => true,
        ]);

        // Create company admin for Busko Transportes
        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Gestor Busko',
            'email' => 'gestor@busko.com',
            'password' => Hash::make('gestor@busko'),
            'type' => 'company_admin',
            'is_active' => true,
        ]);

        // Extra tenants for filter testing
        Tenant::create([
            'uid'               => Str::uuid()->toString(),
            'name'              => 'Alfa Escolar',
            'slug'              => 'alfa-escolar',
            'is_active'         => true,
            'bank_code'         => '033',
            'bank_branch'       => '0002',
            'bank_account'      => '234567-8',
            'bank_account_type' => 'corrente',
            'pix_key'           => 'contato@alfaescolar.com.br',
        ]);

        Tenant::create([
            'uid'               => Str::uuid()->toString(),
            'name'              => 'Beta Transporte',
            'slug'              => 'beta-transporte',
            'is_active'         => true,
            'bank_code'         => '104',
            'bank_branch'       => '0003',
            'bank_account'      => '345678-9',
            'bank_account_type' => 'corrente',
            'pix_key'           => 'financeiro@betatransporte.com.br',
        ]);

        Tenant::create([
            'uid'               => Str::uuid()->toString(),
            'name'              => 'Gama Mobilidade',
            'slug'              => 'gama-mobilidade',
            'is_active'         => true,
            'bank_code'         => '237',
            'bank_branch'       => '0004',
            'bank_account'      => '456789-0',
            'bank_account_type' => 'corrente',
            'pix_key'           => 'ops@gamamobilidade.com.br',
        ]);

        Tenant::create([
            'uid'               => Str::uuid()->toString(),
            'name'              => 'Delta Van',
            'slug'              => 'delta-van',
            'is_active'         => true,
            'bank_code'         => '341',
            'bank_branch'       => '0005',
            'bank_account'      => '567890-1',
            'bank_account_type' => 'corrente',
            'pix_key'           => 'pix@deltavan.com.br',
        ]);

        // Associate driver and guardian
        DriverGuardian::create([
            'driver_id' => $driver->id,
            'guardian_id' => $guardian->id,
            'tenant_id' => $tenant->id,
        ]);

        Passenger::create([
            'tenant_id' => $tenant->id,
            'guardian_id' => $guardian->id,
            'service_type' => 'ida_volta',
            'name' => 'Pedro Passageiro',
            'birth_date' => '2015-03-10',
            'school_grade' => '5º ano',
            'period' => 'tarde',
            'rg' => '123456789',
            'residential_zip' => '01311200',
            'residential_street' => 'Rua das Crianças',
            'residential_number' => '45',
            'residential_complement' => 'Casa 2',
            'residential_neighborhood' => 'Centro',
            'residential_city' => 'São Paulo',
            'residential_state' => 'SP',
            'pickup_zip' => '01311200',
            'pickup_street' => 'Rua das Crianças',
            'pickup_number' => '45',
            'pickup_complement' => 'Portão principal',
            'pickup_neighborhood' => 'Centro',
            'pickup_city' => 'São Paulo',
            'pickup_state' => 'SP',
            'dropoff_zip' => '01310100',
            'dropoff_street' => 'Rua do Saber',
            'dropoff_number' => '100',
            'dropoff_complement' => null,
            'dropoff_neighborhood' => 'Vila Mariana',
            'dropoff_city' => 'São Paulo',
            'dropoff_state' => 'SP',
            'school_name' => 'Escola Municipal Alfa',
            'school_zip' => '01310100',
            'school_street' => 'Rua do Saber',
            'school_number' => '100',
            'school_complement' => null,
            'school_neighborhood' => 'Vila Mariana',
            'school_city' => 'São Paulo',
            'school_state' => 'SP',
            'entry_time' => '13:00',
            'exit_time' => '18:00',
            'monthly_fee' => 450.00,
        ]);

    }
}
