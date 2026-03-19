@extends('layouts.app')

@section('title', 'Passageiros - Busko')

@section('page-title', 'Passageiros')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">Lista de Passageiros</h3>
            <p class="text-sm text-gray-600 mt-1">Passageiros sempre vinculados ao guardião responsável financeiro e devem ser cadastrados a partir da tela de guardiões.</p>
        </div>
    </div>

    @if($passengers->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passageiro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guardião</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serviço</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horários</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($passengers as $passenger)
                        @php
                            $serviceLabel = $passenger->service_type === 'ida'
                                ? 'Ida'
                                : ($passenger->service_type === 'volta' ? 'Volta' : 'Ida e Volta');
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $passenger->name }}</p>
                                <p class="text-xs text-gray-500">RG: {{ $passenger->rg }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $passenger->guardian?->user?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $serviceLabel }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700 capitalize">{{ $passenger->period }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ substr($passenger->entry_time, 0, 5) }} / {{ substr($passenger->exit_time, 0, 5) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" onclick="document.getElementById('passenger-details-{{ $passenger->id }}').showModal()" class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                        Visualizar Detalhes
                                    </button>
                                    <a href="{{ route('portal.passengers.edit', array_merge(['passenger' => $passenger], request()->filled('company_id') ? ['company_id' => request()->integer('company_id')] : [])) }}" class="px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
                                        Editar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @foreach($passengers as $passenger)
            @php
                $serviceLabel = $passenger->service_type === 'ida'
                    ? 'Ida'
                    : ($passenger->service_type === 'volta' ? 'Volta' : 'Ida e Volta');
            @endphp
            <dialog id="passenger-details-{{ $passenger->id }}" class="backdrop:bg-gray-900/40 rounded-2xl p-0 w-full max-w-3xl">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">{{ $passenger->name }}</h4>
                            <p class="text-sm text-gray-600">Guardião: {{ $passenger->guardian?->user?->name ?? '-' }}</p>
                        </div>
                        <button type="button" onclick="document.getElementById('passenger-details-{{ $passenger->id }}').close()" class="px-3 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                            Fechar
                        </button>
                    </div>
                    <div class="p-6 space-y-6 max-h-[80vh] overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                            <div>
                                <p class="text-gray-500">RG</p>
                                <p class="font-medium">{{ $passenger->rg }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Data de Nascimento</p>
                                <p class="font-medium">{{ $passenger->birth_date?->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Serviço</p>
                                <p class="font-medium">{{ $serviceLabel }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Período</p>
                                <p class="font-medium capitalize">{{ $passenger->period }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Série</p>
                                <p class="font-medium">{{ $passenger->school_grade }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Horários</p>
                                <p class="font-medium">{{ substr($passenger->entry_time, 0, 5) }} / {{ substr($passenger->exit_time, 0, 5) }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="border border-gray-200 rounded-xl p-4">
                                <h5 class="text-sm font-semibold text-gray-700 uppercase mb-2">Endereço Residencial</h5>
                                <p class="text-sm text-gray-700">{{ $passenger->residential_street }}, {{ $passenger->residential_number }}</p>
                                <p class="text-sm text-gray-700">{{ $passenger->residential_neighborhood }} - {{ $passenger->residential_city }}/{{ $passenger->residential_state }}</p>
                                <p class="text-sm text-gray-700">CEP: {{ $passenger->residential_zip }}</p>
                                @if($passenger->residential_complement)
                                    <p class="text-sm text-gray-700">Complemento: {{ $passenger->residential_complement }}</p>
                                @endif
                            </div>
                            <div class="border border-gray-200 rounded-xl p-4">
                                <h5 class="text-sm font-semibold text-gray-700 uppercase mb-2">Endereço da Escola</h5>
                                <p class="text-sm font-medium text-gray-900">{{ $passenger->school_name }}</p>
                                <p class="text-sm text-gray-700">{{ $passenger->school_street }}, {{ $passenger->school_number }}</p>
                                <p class="text-sm text-gray-700">{{ $passenger->school_neighborhood }} - {{ $passenger->school_city }}/{{ $passenger->school_state }}</p>
                                <p class="text-sm text-gray-700">CEP: {{ $passenger->school_zip }}</p>
                                @if($passenger->school_complement)
                                    <p class="text-sm text-gray-700">Complemento: {{ $passenger->school_complement }}</p>
                                @endif
                            </div>
                            <div class="border border-gray-200 rounded-xl p-4">
                                <h5 class="text-sm font-semibold text-gray-700 uppercase mb-2">Retirada</h5>
                                <p class="text-sm text-gray-700">{{ $passenger->pickup_street }}, {{ $passenger->pickup_number }}</p>
                                <p class="text-sm text-gray-700">{{ $passenger->pickup_neighborhood }} - {{ $passenger->pickup_city }}/{{ $passenger->pickup_state }}</p>
                                <p class="text-sm text-gray-700">CEP: {{ $passenger->pickup_zip }}</p>
                                @if($passenger->pickup_complement)
                                    <p class="text-sm text-gray-700">Complemento: {{ $passenger->pickup_complement }}</p>
                                @endif
                            </div>
                            <div class="border border-gray-200 rounded-xl p-4">
                                <h5 class="text-sm font-semibold text-gray-700 uppercase mb-2">Entrega</h5>
                                <p class="text-sm text-gray-700">{{ $passenger->dropoff_street }}, {{ $passenger->dropoff_number }}</p>
                                <p class="text-sm text-gray-700">{{ $passenger->dropoff_neighborhood }} - {{ $passenger->dropoff_city }}/{{ $passenger->dropoff_state }}</p>
                                <p class="text-sm text-gray-700">CEP: {{ $passenger->dropoff_zip }}</p>
                                @if($passenger->dropoff_complement)
                                    <p class="text-sm text-gray-700">Complemento: {{ $passenger->dropoff_complement }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </dialog>
        @endforeach

        <div class="p-6 border-t border-gray-200">
            {{ $passengers->links() }}
        </div>
    @else
        <div class="p-10 text-center">
            <p class="text-gray-500">Nenhum passageiro cadastrado ainda.</p>
            <p class="text-sm text-gray-500 mt-2">Use a tela de guardiões para iniciar um novo cadastro de passageiro.</p>
        </div>
    @endif
</div>
@endsection
