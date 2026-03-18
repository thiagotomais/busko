<?php

namespace Tests\Feature;

use App\Models\Tenant;
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
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_inactive_company_blocks_web_login(): void
    {
        $this->seed();

        $tenant = Tenant::where('slug', 'busko-transportes')->firstOrFail();
        $tenant->update(['is_active' => false]);

        $token = 'test-csrf-token';

        $response = $this->withSession(['_token' => $token])
            ->from(route('portal.login'))
            ->post('/portal/login', [
                '_token' => $token,
                'email' => 'thiago@tomais',
                'password' => 'thiago@tomais',
            ]);

        $response->assertRedirect(route('portal.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_seeded_admin_can_login_via_web_form(): void
    {
        $this->seed();

        $token = 'test-csrf-token';

        $response = $this->withSession(['_token' => $token])->post('/portal/login', [
            '_token' => $token,
            'email' => 'admin@busko.com',
            'password' => 'admin@busko',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_inactive_user_blocks_web_login(): void
    {
        $this->seed();

        $user = \App\Models\User::where('email', 'thiago@tomais')->firstOrFail();
        $user->update(['is_active' => false]);

        $token = 'test-csrf-token';

        $response = $this->withSession(['_token' => $token])
            ->from(route('portal.login'))
            ->post('/portal/login', [
                '_token' => $token,
                'email' => 'thiago@tomais',
                'password' => 'thiago@tomais',
            ]);

        $response->assertRedirect(route('portal.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
