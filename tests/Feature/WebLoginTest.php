<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_driver_can_login_via_web_form(): void
    {
        $this->seed();

        $token = 'test-csrf-token';

        $response = $this->withSession(['_token' => $token])->post('/portal/login', [
            '_token' => $token,
            'email' => 'thiago@tomais',
            'password' => 'thiago@tomais',
            'type' => 'driver',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticated();
    }
}
