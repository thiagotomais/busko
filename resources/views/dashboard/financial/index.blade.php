@extends('layouts.app')

@section('title', 'Financeiro - Busko')

@section('page-title', 'Financeiro')

@section('content')
@php
    $companyParams = request()->filled('company')
        ? ['company' => (string) request()->query('company')]
        : (request()->filled('company_id') ? ['company_id' => request()->integer('company_id')] : []);
    $statusLabels = [
        'pending' => 'Pendente',
        'paid' => 'Pago',
        'overdue' => 'Vencido',
        'canceled' => 'Cancelado',
    ];
    $paymentMethodLabels = [
        'pix' => 'PIX',
        'dinheiro' => 'Dinheiro',
        'transferencia' => 'Transferência',
        'cartao' => 'Cartão',
        'boleto' => 'Boleto',
        'outro' => 'Outro',
    ];
@endphp
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">Contas a Receber</h3>
            <p class="text-sm text-gray-600 mt-1">Acompanhe mensalidades por competência, situação e pagamento.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('portal.financial.export', array_merge($companyParams, request()->only(['status', 'competence', 'guardian_id']))) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                Exportar CSV
            </a>
            <a href="{{ route('portal.financial.create', $companyParams) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                + Nova Mensalidade
            </a>
        </div>
    </div>

    <div class="p-6 border-b border-gray-200 bg-blue-50/50">
        <h4 class="text-sm font-semibold text-gray-700 uppercase mb-3">Gerar Competência em Lote</h4>
        <p class="text-xs text-gray-600 mb-3">A geração usa primeiro a mensalidade padrão do passageiro. Se não houver, usa o valor fallback informado abaixo.</p>
        @if(($passengersWithoutMonthlyFeeCount ?? 0) > 0)
            <div class="mb-3 p-3 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 text-sm">
                {{ $passengersWithoutMonthlyFeeCount }} passageiro(s) estão sem mensalidade padrão. Informe um valor fallback para evitar exclusões no lote.
            </div>
        @endif
        <form method="POST" action="{{ route('portal.financial.bulk-store', $companyParams) }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            @csrf
            <div>
                <label for="bulk_competence_month" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Competência</label>
                <input type="month" id="bulk_competence_month" name="competence_month" value="{{ old('competence_month', now()->format('Y-m')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" required>
            </div>
            <div>
                <label for="bulk_due_date" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Vencimento</label>
                <input type="date" id="bulk_due_date" name="due_date" value="{{ old('due_date', now()->addDays(10)->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" required>
            </div>
            <div>
                <label for="bulk_amount" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Valor fallback (R$)</label>
                <input type="number" id="bulk_amount" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Opcional">
            </div>
            <div class="md:col-span-2">
                <label for="bulk_notes" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Observações</label>
                <input type="text" id="bulk_notes" name="notes" value="{{ old('notes') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Opcional para todos os lançamentos gerados">
            </div>
            <div class="md:col-span-5 flex justify-end">
                <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition text-sm">
                    Gerar Mensalidades em Lote
                </button>
            </div>
        </form>
    </div>

    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <form method="GET" action="{{ route('portal.financial.index', $companyParams) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <label for="status" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Status</label>
                <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todos</option>
                    @foreach($statusLabels as $statusKey => $statusLabel)
                        <option value="{{ $statusKey }}" @selected($status === $statusKey)>{{ $statusLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="competence" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Competência</label>
                <input type="month" id="competence" name="competence" value="{{ $competence }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label for="guardian_id" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Guardião</label>
                <select id="guardian_id" name="guardian_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todos</option>
                    @foreach($guardians as $guardian)
                        <option value="{{ $guardian->id }}" @selected((int) $guardianId === (int) $guardian->id)>
                            {{ $guardian->user?->name ?? 'Guardião sem usuário' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-800 transition text-sm">Filtrar</button>
                <a href="{{ route('portal.financial.index', $companyParams) }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm">Limpar</a>
            </div>
        </form>
    </div>

    @if($financialEntries->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Competência</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passageiro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guardião</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pagamento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($financialEntries as $entry)
                        @php($effectiveStatus = $entry->effectiveStatus())
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $entry->competence_month?->format('m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $entry->passenger?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $entry->guardian?->user?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $entry->due_date?->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-semibold">R$ {{ number_format((float) $entry->amount, 2, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $effectiveStatus === 'paid' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $effectiveStatus === 'overdue' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $effectiveStatus === 'pending' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $effectiveStatus === 'canceled' ? 'bg-gray-100 text-gray-700' : '' }}
                                ">
                                    {{ $statusLabels[$effectiveStatus] ?? ucfirst($effectiveStatus) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                @if($entry->status === 'paid')
                                    <p>{{ $paymentMethodLabels[$entry->payment_method] ?? ucfirst((string) $entry->payment_method) }}</p>
                                    <p class="text-xs text-gray-500">{{ $entry->paid_at?->format('d/m/Y H:i') }}</p>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                @if($entry->status !== 'paid')
                                    <form action="{{ route('portal.financial.mark-paid', array_merge(['financialEntry' => $entry], $companyParams)) }}" method="POST" class="space-y-2 min-w-48">
                                        @csrf
                                        <select name="payment_method" class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs" required>
                                            <option value="">Forma de pagamento</option>
                                            @foreach($paymentMethodLabels as $methodKey => $methodLabel)
                                                <option value="{{ $methodKey }}">{{ $methodLabel }}</option>
                                            @endforeach
                                        </select>
                                        <input type="datetime-local" name="paid_at" value="{{ now()->format('Y-m-d\\TH:i') }}" class="w-full px-2 py-1.5 border border-gray-300 rounded text-xs">
                                        <button type="submit" class="w-full px-3 py-2 rounded bg-green-600 text-white hover:bg-green-700 transition text-xs">Marcar como pago</button>
                                    </form>
                                @else
                                    <form action="{{ route('portal.financial.mark-pending', array_merge(['financialEntry' => $entry], $companyParams)) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-xs">Estornar para pendente</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-gray-200">
            {{ $financialEntries->links() }}
        </div>
    @else
        <div class="p-10 text-center">
            <p class="text-gray-500">Nenhuma mensalidade cadastrada.</p>
            <p class="text-sm text-gray-500 mt-2">Crie o primeiro lançamento financeiro para começar o controle de recebimentos.</p>
        </div>
    @endif
</div>
@endsection
