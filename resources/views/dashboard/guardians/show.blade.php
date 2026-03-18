@extends('layouts.app')

@section('title', 'Detalhes do Guardião - Busko')

@section('page-title', 'Detalhes do Guardião')

@section('content')
@php($guardianUser = $guardian->user)
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Guardian Info -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">{{ $guardianUser?->name ?? 'Usuario nao vinculado' }}</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Info -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 uppercase mb-3">Informações Pessoais</h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-gray-600 text-sm">Email</p>
                            <p class="font-medium">{{ $guardianUser?->email ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm">Tipo de Usuário</p>
                            <p class="font-medium capitalize">{{ $guardianUser?->type?->value ?? $guardianUser?->type ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Guardian Info -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 uppercase mb-3">Dados</h4>
                    <div class="space-y-3">
                        <div>
                            <p class="text-gray-600 text-sm">CPF</p>
                            <p class="font-medium"><code class="bg-gray-100 px-2 py-1 rounded">{{ $guardian->cpf }}</code></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address -->
        @if($guardian->address)
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-4">📍 Endereço</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600 text-sm">Rua</p>
                        <p class="font-medium">{{ $guardian->address->street }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Número</p>
                        <p class="font-medium">{{ $guardian->address->number }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Cidade</p>
                        <p class="font-medium">{{ $guardian->address->city }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Estado</p>
                        <p class="font-medium">{{ strtoupper($guardian->address->state) }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Associated Drivers -->
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">🚗 Motoristas Associados</h4>
            @if($guardian->drivers->count() > 0)
                <div class="space-y-3">
                    @foreach($guardian->drivers as $driver)
                        <div class="p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition">
                            <a href="{{ route('portal.drivers.show', $driver) }}" class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $driver->user?->name ?? 'Usuario nao vinculado' }}</p>
                                    <p class="text-sm text-gray-600">{{ $driver->user?->email ?? '-' }}</p>
                                </div>
                                <span class="text-blue-600">→</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-4">Nenhum motorista associado.</p>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div class="lg:col-span-1">
        <!-- Tenant Info -->
        @if($guardian->tenant)
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h4 class="text-sm font-semibold text-gray-700 uppercase mb-3">Empresa</h4>
                <p class="font-medium text-gray-900">{{ $guardian->tenant->name ?? 'N/A' }}</p>
                <p class="text-sm text-gray-600 mt-2">ID: <code class="bg-gray-100 px-2 py-1 rounded">{{ $guardian->tenant->id }}</code></p>
            </div>
        @endif

        <!-- Dates -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h4 class="text-sm font-semibold text-gray-700 uppercase mb-3">Datas</h4>
            <div class="space-y-2 text-sm">
                <div>
                    <p class="text-gray-600">Criado em</p>
                    <p class="font-medium">{{ $guardian->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-gray-600">Atualizado em</p>
                    <p class="font-medium">{{ $guardian->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <a href="{{ route('portal.guardians.index') }}" class="w-full inline-block text-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition">
            ← Voltar
        </a>
    </div>
</div>
@endsection
