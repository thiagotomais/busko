<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Guardian;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardShowViewFallbackTest extends TestCase
{
    public function test_driver_show_view_renders_when_driver_user_is_missing(): void
    {
        $driver = new Driver([
            'slug' => '9b2c310d-610e-4463-9a2d-6c508aaed593',
            'cpf' => '11144477735',
            'cnh' => '98765432100',
        ]);

        $driver->setAttribute('created_at', Carbon::parse('2026-03-18 10:00:00'));
        $driver->setAttribute('updated_at', Carbon::parse('2026-03-18 10:00:00'));
        $driver->setRelation('guardians', collect());

        $html = view('dashboard.drivers.show', compact('driver'))->render();

        $this->assertStringContainsString('Usuario nao vinculado', $html);
        $this->assertStringContainsString('Nenhum guardião associado.', $html);
    }

    public function test_guardian_show_view_renders_when_guardian_user_is_missing(): void
    {
        $guardian = new Guardian([
            'slug' => '7c7b5a69-94ea-4ffb-9af8-69d58be7a2d1',
            'cpf' => '11122233344',
        ]);

        $guardian->setAttribute('created_at', Carbon::parse('2026-03-18 10:00:00'));
        $guardian->setAttribute('updated_at', Carbon::parse('2026-03-18 10:00:00'));
        $guardian->setRelation('drivers', collect());

        $html = view('dashboard.guardians.show', compact('guardian'))->render();

        $this->assertStringContainsString('Usuario nao vinculado', $html);
        $this->assertStringContainsString('Nenhum motorista associado.', $html);
    }
}
