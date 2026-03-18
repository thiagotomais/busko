@extends('layouts.app')

@section('title', 'Dashboard - Busko')

@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
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
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Ações Rápidas</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <a href="{{ route('portal.drivers.index') }}" class="block p-4 border-2 border-blue-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
            <p class="text-blue-600 font-semibold">📋 Gerenciar Motoristas</p>
            <p class="text-gray-600 text-sm">Ver e editar dados de motoristas</p>
        </a>
        
        <a href="{{ route('portal.guardians.index') }}" class="block p-4 border-2 border-green-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition">
            <p class="text-green-600 font-semibold">📋 Gerenciar Guardiões</p>
            <p class="text-gray-600 text-sm">Administrar dados de guardiões</p>
        </a>
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
