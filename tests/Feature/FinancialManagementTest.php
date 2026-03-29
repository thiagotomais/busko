<?php

namespace Tests\Feature;

use App\Models\FinancialEntry;
use App\Models\Passenger;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_manager_can_create_monthly_financial_entry(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $passenger = Passenger::where('name', 'Pedro Passageiro')->firstOrFail();
        $token = 'financial-create-token';

        $createPage = $this->actingAs($manager)
            ->get(route('portal.financial.create'));

        $createPage->assertOk();
        $createPage->assertSee('Cadastrar Mensalidade');
        $createPage->assertSee($passenger->name);

        $response = $this->actingAs($manager)
            ->withSession(['_token' => $token])
            ->post(route('portal.financial.store'), [
                '_token' => $token,
                'passenger_id' => $passenger->id,
                'competence_month' => '2026-03',
                'amount' => '450.00',
                'due_date' => '2026-03-10',
                'notes' => 'Mensalidade de março',
            ]);

        $response->assertRedirect(route('portal.financial.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('financial_entries', [
            'tenant_id' => $manager->tenant_id,
            'guardian_id' => $passenger->guardian_id,
            'passenger_id' => $passenger->id,
            'status' => 'pending',
        ]);
    }

    public function test_cannot_create_duplicate_competence_for_same_passenger(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $passenger = Passenger::where('name', 'Pedro Passageiro')->firstOrFail();

        FinancialEntry::create([
            'tenant_id' => $manager->tenant_id,
            'guardian_id' => $passenger->guardian_id,
            'passenger_id' => $passenger->id,
            'competence_month' => '2026-03-01',
            'amount' => 430.00,
            'due_date' => '2026-03-10',
            'status' => 'pending',
        ]);

        $token = 'financial-duplicate-token';

        $response = $this->actingAs($manager)
            ->withSession(['_token' => $token])
            ->post(route('portal.financial.store'), [
                '_token' => $token,
                'passenger_id' => $passenger->id,
                'competence_month' => '2026-03',
                'amount' => '450.00',
                'due_date' => '2026-03-15',
            ]);

        $response->assertSessionHasErrors('passenger_id');
        $this->assertDatabaseCount('financial_entries', 1);
    }

    public function test_company_manager_can_mark_entry_as_paid_and_revert_to_pending(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $passenger = Passenger::where('name', 'Pedro Passageiro')->firstOrFail();

        $entry = FinancialEntry::create([
            'tenant_id' => $manager->tenant_id,
            'guardian_id' => $passenger->guardian_id,
            'passenger_id' => $passenger->id,
            'competence_month' => '2026-04-01',
            'amount' => 470.00,
            'due_date' => now()->addDays(2)->toDateString(),
            'status' => 'pending',
        ]);

        $markPaidResponse = $this->actingAs($manager)
            ->post(route('portal.financial.mark-paid', $entry), [
                'payment_method' => 'pix',
                'paid_at' => '2026-04-05 09:30:00',
            ]);

        $markPaidResponse->assertRedirect(route('portal.financial.index'));
        $markPaidResponse->assertSessionHas('success');

        $this->assertDatabaseHas('financial_entries', [
            'id' => $entry->id,
            'status' => 'paid',
            'payment_method' => 'pix',
        ]);

        $markPendingResponse = $this->actingAs($manager)
            ->post(route('portal.financial.mark-pending', $entry));

        $markPendingResponse->assertRedirect(route('portal.financial.index'));
        $markPendingResponse->assertSessionHas('success');

        $this->assertDatabaseHas('financial_entries', [
            'id' => $entry->id,
            'status' => 'pending',
            'payment_method' => null,
            'paid_at' => null,
        ]);
    }

    public function test_financial_index_can_filter_overdue_entries(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $passenger = Passenger::where('name', 'Pedro Passageiro')->firstOrFail();

        FinancialEntry::create([
            'tenant_id' => $manager->tenant_id,
            'guardian_id' => $passenger->guardian_id,
            'passenger_id' => $passenger->id,
            'competence_month' => '2026-02-01',
            'amount' => 420.00,
            'due_date' => now()->subDays(5)->toDateString(),
            'status' => 'pending',
        ]);

        FinancialEntry::create([
            'tenant_id' => $manager->tenant_id,
            'guardian_id' => $passenger->guardian_id,
            'passenger_id' => $passenger->id,
            'competence_month' => '2026-01-01',
            'amount' => 410.00,
            'due_date' => now()->subDays(15)->toDateString(),
            'status' => 'paid',
            'paid_at' => now()->subDays(10),
            'payment_method' => 'pix',
        ]);

        $response = $this->actingAs($manager)
            ->get(route('portal.financial.index', ['status' => 'overdue']));

        $response->assertOk();
        $response->assertSee('Vencido');
    }

    public function test_financial_index_warns_when_passengers_have_no_monthly_fee(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        Passenger::where('name', 'Pedro Passageiro')->firstOrFail()->update(['monthly_fee' => null]);

        $response = $this->actingAs($manager)
            ->get(route('portal.financial.index'));

        $response->assertOk();
        $response->assertSee('sem mensalidade padrão');
    }

    public function test_global_admin_sees_company_selector_before_financial_area(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@busko.com')->firstOrFail();

        $response = $this->actingAs($admin)
            ->get(route('portal.financial.index'));

        $response->assertOk();
        $response->assertSee('Selecione uma empresa para continuar');
        $response->assertSee('Abrir financeiro');
    }

    public function test_global_admin_can_open_financial_area_using_company_uid(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@busko.com')->firstOrFail();
        $tenant = Tenant::where('slug', 'busko-transportes')->firstOrFail();

        $response = $this->actingAs($admin)
            ->get(route('portal.financial.index', ['company' => $tenant->uid]));

        $response->assertOk();
        $response->assertSee('Contas a Receber');
    }

    public function test_company_manager_can_generate_monthly_entries_in_bulk(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $passenger = Passenger::where('name', 'Pedro Passageiro')->firstOrFail();

        FinancialEntry::create([
            'tenant_id' => $manager->tenant_id,
            'guardian_id' => $passenger->guardian_id,
            'passenger_id' => $passenger->id,
            'competence_month' => '2026-05-01',
            'amount' => 500.00,
            'due_date' => '2026-05-10',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($manager)
            ->post(route('portal.financial.bulk-store'), [
                'competence_month' => '2026-05',
                'amount' => '550.00',
                'due_date' => '2026-05-15',
                'notes' => 'Geração em lote maio',
            ]);

        $response->assertRedirect(route('portal.financial.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('financial_entries', 1);
    }

    public function test_bulk_generation_uses_passenger_monthly_fee_when_fallback_not_informed(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $passenger = Passenger::where('name', 'Pedro Passageiro')->firstOrFail();

        $response = $this->actingAs($manager)
            ->post(route('portal.financial.bulk-store'), [
                'competence_month' => '2026-07',
                'due_date' => '2026-07-10',
                'notes' => 'Lote com valor do passageiro',
            ]);

        $response->assertRedirect(route('portal.financial.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('financial_entries', [
            'tenant_id' => $manager->tenant_id,
            'passenger_id' => $passenger->id,
            'competence_month' => '2026-07-01',
            'amount' => 450.00,
            'status' => 'pending',
        ]);
    }

    public function test_company_manager_can_export_financial_entries_to_csv(): void
    {
        $this->seed();

        $manager = User::where('email', 'thiago@tomais')->firstOrFail();
        $passenger = Passenger::where('name', 'Pedro Passageiro')->firstOrFail();

        FinancialEntry::create([
            'tenant_id' => $manager->tenant_id,
            'guardian_id' => $passenger->guardian_id,
            'passenger_id' => $passenger->id,
            'competence_month' => '2026-06-01',
            'amount' => 530.00,
            'due_date' => '2026-06-10',
            'status' => 'pending',
            'notes' => 'Lançamento exportável',
        ]);

        $response = $this->actingAs($manager)
            ->get(route('portal.financial.export', ['status' => 'pending', 'competence' => '2026-06']));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Competencia;Passageiro;Guardiao;Vencimento;Valor;Status;"Forma de pagamento";"Pago em";Observacoes', $content);
        $this->assertStringContainsString('Pedro Passageiro', $content);
        $this->assertStringContainsString('Lançamento exportável', $content);
    }
}
