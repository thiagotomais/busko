@extends('layouts.app')

@section('title', 'Detalhes da Rota - Busko')

@section('page-title', 'Detalhes da Rota')

@php
    $companyParams = request()->filled('company')
        ? ['company' => (string) request()->query('company')]
        : (request()->filled('company_id') ? ['company_id' => request()->integer('company_id')] : []);
    $weekdayLabels = \App\Models\TransportRoute::weekdayLabels();
    $enabledWeekdays = collect($weekdayLabels)
        ->filter(fn ($label, $field) => $transportRoute->{$field})
        ->values();
    $isOutbound = $transportRoute->direction === 'ida';
@endphp

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $transportRoute->name }}</h3>
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $transportRoute->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                        {{ $transportRoute->is_active ? 'Rota ativa' : 'Rota inativa' }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 mt-2">Motorista responsável: <span class="font-medium text-gray-800">{{ $transportRoute->driver?->user?->name ?? '-' }}</span></p>
                <p class="text-sm text-gray-600 mt-1">
                    Operação: <span class="font-medium text-gray-800">{{ ucfirst($transportRoute->direction) }}</span>
                    · Período: <span class="font-medium text-gray-800 capitalize">{{ $transportRoute->period }}</span>
                    · Passageiros: <span class="font-medium text-gray-800">{{ $transportRoute->passengers->count() }}</span>
                </p>
                <p class="text-sm text-gray-600 mt-1">
                    Veículo: <span class="font-medium text-gray-800">{{ $transportRoute->vehicle_name ?: 'Não informado' }}</span>
                    @if($transportRoute->vehicle_plate)
                        · Placa: <span class="font-medium text-gray-800">{{ strtoupper($transportRoute->vehicle_plate) }}</span>
                    @endif
                </p>
                @if($transportRoute->notes)
                    <p class="text-sm text-gray-600 mt-3 max-w-3xl">{{ $transportRoute->notes }}</p>
                @endif
            </div>

            <div class="flex gap-3">
                <a href="{{ route('portal.transport-routes.index', $companyParams) }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Voltar</a>
                <a href="{{ route('portal.transport-routes.edit', array_merge(['transportRoute' => $transportRoute], $companyParams)) }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">Editar Rota</a>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap gap-2">
            @foreach($enabledWeekdays as $label)
                <span class="px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-700">{{ $label }}</span>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
            <div class="mb-5">
                <h4 class="text-lg font-semibold text-gray-800">Mapa Operacional</h4>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $isOutbound ? 'Sequência de embarque dos passageiros até a escola.' : 'Sequência de saída da escola até a entrega dos passageiros.' }}
                </p>
            </div>

            @if($transportRoute->passengers->isNotEmpty())
                <div class="space-y-4">
                    @foreach($transportRoute->passengers as $passenger)
                        @php
                            $originTitle = $isOutbound ? 'Embarque' : 'Origem Escolar';
                            $destinationTitle = $isOutbound ? 'Destino Escolar' : 'Desembarque';
                            $originLine1 = $isOutbound
                                ? $passenger->pickup_street . ', ' . $passenger->pickup_number
                                : $passenger->school_name;
                            $originLine2 = $isOutbound
                                ? $passenger->pickup_neighborhood . ' - ' . $passenger->pickup_city . '/' . $passenger->pickup_state
                                : $passenger->school_street . ', ' . $passenger->school_number . ' - ' . $passenger->school_city . '/' . $passenger->school_state;
                            $destinationLine1 = $isOutbound
                                ? $passenger->school_name
                                : $passenger->dropoff_street . ', ' . $passenger->dropoff_number;
                            $destinationLine2 = $isOutbound
                                ? $passenger->school_street . ', ' . $passenger->school_number . ' - ' . $passenger->school_city . '/' . $passenger->school_state
                                : $passenger->dropoff_neighborhood . ' - ' . $passenger->dropoff_city . '/' . $passenger->dropoff_state;
                        @endphp
                        <div class="border border-gray-200 rounded-xl p-5">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-semibold shrink-0">
                                        {{ $passenger->pivot->stop_order }}
                                    </div>
                                    <div>
                                        <h5 class="text-lg font-semibold text-gray-900">{{ $passenger->name }}</h5>
                                        <p class="text-sm text-gray-600 mt-1">Guardião: {{ $passenger->guardian?->user?->name ?? '-' }}</p>
                                        <p class="text-sm text-gray-600">Serviço: {{ $passenger->service_type === 'ida_volta' ? 'Ida e Volta' : ucfirst($passenger->service_type) }} · Horário base: {{ substr($passenger->entry_time, 0, 5) }} / {{ substr($passenger->exit_time, 0, 5) }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('portal.passengers.edit', array_merge(['passenger' => $passenger], $companyParams)) }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                    Editar passageiro →
                                </a>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-[1fr_auto_1fr] gap-4 mt-5 items-center">
                                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ $originTitle }}</p>
                                    <p class="font-medium text-gray-900">{{ $originLine1 }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $originLine2 }}</p>
                                </div>
                                <div class="text-center text-blue-600 font-semibold text-xl">→</div>
                                <div class="border border-gray-200 rounded-lg p-4 bg-blue-50">
                                    <p class="text-xs font-semibold text-blue-600 uppercase mb-2">{{ $destinationTitle }}</p>
                                    <p class="font-medium text-gray-900">{{ $destinationLine1 }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $destinationLine2 }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="border border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
                    Nenhum passageiro vinculado a esta rota.
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h4 class="text-sm font-semibold text-gray-700 uppercase mb-4">Resumo Operacional</h4>
                <div class="space-y-3 text-sm text-gray-700">
                    <div>
                        <p class="text-gray-500">Fluxo</p>
                        <p class="font-medium">{{ $isOutbound ? 'Casa/Ponto → Escola' : 'Escola → Entrega' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Primeira parada</p>
                        <p class="font-medium">{{ optional($transportRoute->passengers->first())->name ?? 'Sem passageiro' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Última parada</p>
                        <p class="font-medium">{{ optional($transportRoute->passengers->last())->name ?? 'Sem passageiro' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Dias ativos</p>
                        <p class="font-medium">{{ $enabledWeekdays->isNotEmpty() ? $enabledWeekdays->implode(', ') : 'Não informado' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h4 class="text-sm font-semibold text-gray-700 uppercase mb-4">Sequência de Paradas</h4>
                @if($transportRoute->passengers->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($transportRoute->passengers as $passenger)
                            <div class="flex items-center gap-3 border border-gray-200 rounded-lg p-3">
                                <div class="w-8 h-8 rounded-full bg-gray-900 text-white flex items-center justify-center text-sm font-semibold">{{ $passenger->pivot->stop_order }}</div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $passenger->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $isOutbound ? $passenger->pickup_neighborhood : $passenger->dropoff_neighborhood }} · {{ $passenger->guardian?->user?->name ?? '-' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">Nenhuma parada cadastrada.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection