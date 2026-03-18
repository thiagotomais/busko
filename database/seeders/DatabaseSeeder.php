<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Driver;
use App\Models\DriverGuardian;
use App\Models\Guardian;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'name' => 'Busko Transportes',
            'slug' => 'busko-transportes',
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
            'name' => 'Thiago Driver',
            'email' => 'thiago@tomais',
            'password' => Hash::make('thiago@tomais'),
            'type' => 'driver',
        ]);

        $driver = Driver::create([
            'tenant_id' => $tenant->id,
            'user_id' => $driverUser->id,
            'cpf' => '123.456.789-00',
            'cnh' => '12345678900',
            'address_id' => $driverAddress->id,
        ]);

        // Create guardian user and profile
        $guardianUser = User::create([
            'name' => 'Maria Guardian',
            'email' => 'guardian@test.com',
            'password' => Hash::make('password'),
            'type' => 'guardian',
        ]);

        $guardian = Guardian::create([
            'tenant_id' => $tenant->id,
            'user_id' => $guardianUser->id,
            'cpf' => '987.654.321-00',
            'address_id' => $guardianAddress->id,
        ]);

        // Associate driver and guardian
        DriverGuardian::create([
            'driver_id' => $driver->id,
            'guardian_id' => $guardian->id,
            'tenant_id' => $tenant->id,
        ]);

        // Create additional test data
        User::factory(5)->create(['type' => 'driver']);
        User::factory(5)->create(['type' => 'guardian']);
    }
}
