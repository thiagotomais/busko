@extends('layouts.app')

@section('title', 'Rotas - Busko')

@section('page-title', 'Rotas')

@php($weekdayLabels = \App\Models\TransportRoute::weekdayLabels())
@php($companyParams = request()->filled('company') ? ['company' => (string) request()->query('company')] : (request()->filled('company_id') ? ['company_id' => request()->integer('company_id')] : []))

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">Planejamento de Rotas</h3>
            <p class="text-sm text-gray-600 mt-1">Crie rotas por motorista, direção e período e organize a ordem dos passageiros.</p>
        </div>
        <a href="{{ route('portal.transport-routes.create', $companyParams) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            + Nova Rota
        </a>
    </div>

    @if($transportRoutes->count() > 0)
        <div class="divide-y divide-gray-200">
            @foreach($transportRoutes as $transportRoute)
                <div class="p-6">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-3 flex-wrap">
                                <h4 class="text-lg font-semibold text-gray-900">{{ $transportRoute->name }}</h4>
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $transportRoute->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $transportRoute->is_active ? 'Ativa' : 'Inativa' }}
                                </span>
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ ucfirst($transportRoute->direction) }}</span>
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 capitalize">{{ $transportRoute->period }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">
                                Motorista: <span class="font-medium text-gray-800">{{ $transportRoute->driver?->user?->name ?? '-' }}</span>
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                Veículo: <span class="font-medium text-gray-800">{{ $transportRoute->vehicle_name ?: 'Não informado' }}</span>
                                @if($transportRoute->vehicle_plate)
                                    · Placa: <span class="font-medium text-gray-800">{{ strtoupper($transportRoute->vehicle_plate) }}</span>
                                @endif
                            </p>
                            <div class="flex flex-wrap gap-2 mt-3">
                                @foreach($weekdayLabels as $field => $label)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $transportRoute->{$field} ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400' }}">
                                        {{ $label }}
                                    </span>
                                @endforeach
                            </div>
                            @if($transportRoute->notes)
                                <p class="text-sm text-gray-600 mt-3">{{ $transportRoute->notes }}</p>
                            @endif
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('portal.transport-routes.show', array_merge(['transportRoute' => $transportRoute], $companyParams)) }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                Visualizar
                            </a>
                            <a href="{{ route('portal.transport-routes.edit', array_merge(['transportRoute' => $transportRoute], $companyParams)) }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                Editar
                            </a>
                        </div>
                    </div>

                    <div class="mt-5 border border-gray-200 rounded-lg overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-700 uppercase">Passageiros Vinculados</p>
                            <span class="text-sm text-gray-600">{{ $transportRoute->passengers->count() }} total</span>
                        </div>

                        @if($transportRoute->passengers->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-white">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordem</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passageiro</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guardião</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serviço</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($transportRoute->passengers as $passenger)
                                            <tr>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $passenger->pivot->stop_order }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-700">{{ $passenger->name }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-700">{{ $passenger->guardian?->user?->name ?? '-' }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-700">{{ $passenger->service_type === 'ida_volta' ? 'Ida e Volta' : ucfirst($passenger->service_type) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="px-4 py-6 text-sm text-gray-500">Nenhum passageiro vinculado a esta rota.</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p-6 border-t border-gray-200">
            {{ $transportRoutes->links() }}
        </div>
    @else
        <div class="p-10 text-center">
            <p class="text-gray-500 mb-4">Nenhuma rota cadastrada ainda.</p>
            <a href="{{ route('portal.transport-routes.create', $companyParams) }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Cadastrar Primeira Rota
            </a>
        </div>
    @endif
</div>
@endsection