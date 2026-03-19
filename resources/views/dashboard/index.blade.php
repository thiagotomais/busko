@extends('layouts.app')

@section('title', 'Dashboard - Busko')

@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Stats Cards -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total de Motoristas</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['drivers'] }}</p>
            </div>
            <div class="text-4xl">🚗</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total de Guardiões</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['guardians'] }}</p>
            </div>
            <div class="text-4xl">👥</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total de Usuários</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['users'] }}</p>
            </div>
            <div class="text-4xl">👤</div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600 text-sm font-medium">Total de Passageiros</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['passengers'] }}</p>
            </div>
            <div class="text-4xl">🚌</div>
        </div>
    </div>
</div>

@if($tenant)
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-gray-600 text-sm font-medium">Empresa de Transportes</p>
                <p class="text-2xl font-bold text-gray-800">{{ $tenant->name }}</p>
                <p class="text-sm text-gray-600 mt-1">Slug: <code class="bg-gray-100 px-2 py-1 rounded">{{ $tenant->slug }}</code></p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $tenant->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $tenant->is_active ? 'Ativa' : 'Inativa' }}
                </span>
                <a href="{{ route('portal.company.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition">
                    Gerenciar Empresa
                </a>
            </div>
        </div>
    </div>
@endif

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Ações Rápidas</h3>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <a href="{{ route('portal.drivers.index') }}" class="block p-4 border-2 border-blue-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
            <p class="text-blue-600 font-semibold">📋 Gerenciar Motoristas</p>
            <p class="text-gray-600 text-sm">Ver e editar dados de motoristas</p>
        </a>
        
        <a href="{{ route('portal.guardians.index') }}" class="block p-4 border-2 border-green-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition">
            <p class="text-green-600 font-semibold">📋 Gerenciar Guardiões</p>
            <p class="text-gray-600 text-sm">Administrar dados de guardiões</p>
        </a>

        <a href="{{ route('portal.company.index') }}" class="block p-4 border-2 border-amber-200 rounded-lg hover:border-amber-500 hover:bg-amber-50 transition">
            <p class="text-amber-700 font-semibold">🏢 Gerenciar Empresa</p>
            <p class="text-gray-600 text-sm">Editar dados e controlar o status da transportadora</p>
        </a>

        @if(auth()->user()?->type === \App\Enums\UserType::ADMIN || (auth()->user()?->type === \App\Enums\UserType::DRIVER && auth()->user()?->is_company_manager))
            <a href="{{ route('portal.passengers.index') }}" class="block p-4 border-2 border-indigo-200 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition">
                <p class="text-indigo-700 font-semibold">🚌 Gerenciar Passageiros</p>
                <p class="text-gray-600 text-sm">Cadastrar e vincular passageiros aos guardiões</p>
            </a>
        @endif

        @if(auth()->user()?->type === \App\Enums\UserType::ADMIN || (auth()->user()?->type === \App\Enums\UserType::DRIVER && auth()->user()?->is_company_manager))
            <a href="{{ route('portal.users.index') }}" class="block p-4 border-2 border-purple-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition">
                <p class="text-purple-700 font-semibold">🧾 Gestão de Usuários</p>
                <p class="text-gray-600 text-sm">Criar, editar e ativar/desativar usuários</p>
            </a>
        @endif
    </div>
</div>

<!-- Welcome Message -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-2">Bem-vindo, {{ auth()->user()->name }}! 👋</h3>
    <p class="text-gray-600">
        Esta é a sua central de gestão. Use o menu lateral para navegar entre as diferentes seções do sistema.
    </p>
</div>
@endsection
