<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Guardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    public function test_driver_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/drivers/register', [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'cpf' => '11144477735',  // Valid CPF
            'cnh' => '98765432100',  // Valid CNH format
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'user' => ['id', 'name', 'email', 'type'],
                'driver' => ['id', 'cpf', 'cnh'],
                'token',
                'tenant_id',
            ],
        ]);

        $this->assertTrue(User::where('email', 'joao@example.com')->exists());
        $this->assertTrue(Driver::where('cpf', '11144477735')->exists());
    }

    public function test_guardian_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/guardians/register', [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'cpf' => '11122233344',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'user' => ['id', 'name', 'email', 'type'],
                'guardian' => ['id', 'cpf'],
                'token',
                'tenant_id',
            ],
        ]);

        $this->assertTrue(User::where('email', 'maria@example.com')->exists());
        $this->assertTrue(Guardian::where('cpf', '11122233344')->exists());
    }

    public function test_driver_can_login(): void
    {
        // Create a driver first
        $this->postJson('/api/v1/auth/drivers/register', [
            'name' => 'Pedro',
            'email' => 'pedro@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'cpf' => '11144477735',
            'cnh' => '98765432100',
        ]);

        // Login
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'pedro@example.com',
            'password' => 'password123',
            'type' => 'driver',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'user' => ['id', 'name', 'email', 'type'],
                'token',
                'tenant_id',
            ],
        ]);
    }

    public function test_invalid_credentials_return_error(): void
    {
        $this->postJson('/api/v1/auth/drivers/register', [
            'name' => 'Paulo',
            'email' => 'paulo@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'cpf' => '12345678909',
            'cnh' => '11223344556',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'paulo@example.com',
            'password' => 'wrongpassword',
            'type' => 'driver',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_access_me_endpoint(): void
    {
        $registerResponse = $this->postJson('/api/v1/auth/drivers/register', [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'cpf' => '11144477735',
            'cnh' => '98765432100',
        ]);

        $token = $registerResponse->json('data.token');

        $response = $this->getJson('/api/v1/auth/me', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'user' => ['id', 'name', 'email', 'type'],
                'tenant_id',
            ],
        ]);
    }

    public function test_unauthenticated_cannot_access_me_endpoint(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_logout_revokes_token(): void
    {
        $registerResponse = $this->postJson('/api/v1/auth/drivers/register', [
            'name' => 'Carlos',
            'email' => 'carlos@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'cpf' => '11144477735',
            'cnh' => '98765432100',
        ]);

        $token = $registerResponse->json('data.token');

        // Logout
        $response = $this->postJson('/api/v1/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Logged out successfully']);

        // Verify token is deleted from database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'token' => hash('sha256', $token)
        ]);
    }
}


