@extends('layouts.app')

@section('title', 'Detalhes do Motorista - Busko')

@section('page-title', 'Detalhes do Motorista')

@section('content')
@php($driverUser = $driver->user)
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Driver Info -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">{{ $driverUser?->name ?? 'Usuario nao vinculado' }}</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Info -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 uppercase mb-3">Informações Pessoais</h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-gray-600 text-sm">Email</p>
                            <p class="font-medium">{{ $driverUser?->email ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Tipo de Usuário</p>
                            <p class="font-medium capitalize">{{ $driverUser?->type?->value ?? $driverUser?->type ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Driver Info -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 uppercase mb-3">Dados Profissionais</h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-gray-600 text-sm">CPF</p>
                            <p class="font-medium"><code class="bg-gray-100 px-2 py-1 rounded">{{ $driver->cpf }}</code></p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">CNH</p>
                            <p class="font-medium"><code class="bg-gray-100 px-2 py-1 rounded">{{ $driver->cnh }}</code></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address -->
        @if($driver->address)
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">📍 Endereço</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600 text-sm">Rua</p>
                        <p class="font-medium">{{ $driver->address->street }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Número</p>
                        <p class="font-medium">{{ $driver->address->number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Cidade</p>
                        <p class="font-medium">{{ $driver->address->city }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Estado</p>
                        <p class="font-medium">{{ strtoupper($driver->address->state) }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Guardians -->
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">👥 Guardiões Associados</h4>
            @if($driver->guardians->count() > 0)
                <div class="space-y-3">
                    @foreach($driver->guardians as $guardian)
                        <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition">
                            <a href="{{ route('portal.guardians.show', $guardian) }}" class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $guardian->user?->name ?? 'Usuario nao vinculado' }}</p>
                                    <p class="text-sm text-gray-600">{{ $guardian->user?->email ?? '-' }}</p>
                                </div>
                                <span class="text-blue-600">→</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Nenhum guardião associado.</p>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1">
        <!-- Tenant Info -->
        @if($driver->tenant)
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h4 class="text-sm font-semibold text-gray-700 uppercase mb-3">Empresa</h4>
                <p class="font-medium text-gray-900">{{ $driver->tenant->name ?? 'N/A' }}</p>
                <p class="text-sm text-gray-600 mt-2">ID: <code class="bg-gray-100 px-2 py-1 rounded">{{ $driver->tenant->id }}</code></p>
            </div>
        @endif

        <!-- Dates -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h4 class="text-sm font-semibold text-gray-700 uppercase mb-3">Datas</h4>
            <div class="space-y-2 text-sm">
                <div>
                    <p class="text-gray-600">Criado em</p>
                    <p class="font-medium">{{ $driver->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Atualizado em</p>
                    <p class="font-medium">{{ $driver->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <a href="{{ route('portal.drivers.index') }}" class="w-full inline-block text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition">
            ← Voltar
        </a>
    </div>
</div>
@endsection
