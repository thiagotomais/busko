@extends('layouts.app')

@section('title', 'Editar Empresa - Busko')

@section('page-title', 'Editar Empresa')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
    <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800">{{ $tenant->name }}</h3>
        <p class="text-sm text-gray-600 mt-1">Ajuste os dados da empresa selecionada.</p>
    </div>

    <form action="{{ route('portal.company.admin-update', $tenant) }}" method="POST" class="space-y-5">
        @csrf
        @method('PATCH')

        <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nome da Empresa</label>
            <input type="text" id="name" name="name" value="{{ old('name', $tenant->name) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 @error('name') border-red-500 @enderror" required>
            @error('name')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="slug" class="block text-sm font-semibold text-gray-700 mb-2">Slug</label>
            <input type="text" id="slug" name="slug" value="{{ old('slug', $tenant->slug) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 @error('slug') border-red-500 @enderror" required>
            @error('slug')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-3 gap-4 bg-gray-50 rounded-lg p-4">
            <div>
                <p class="text-xs text-gray-500 uppercase">Motoristas</p>
                <p class="text-lg font-semibold text-gray-800">{{ $tenant->drivers_count }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Guardiões</p>
                <p class="text-lg font-semibold text-gray-800">{{ $tenant->guardians_count }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase">Usuários</p>
                <p class="text-lg font-semibold text-gray-800">{{ $tenant->users_count }}</p>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('portal.company.index') }}" class="px-5 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Cancelar</a>
            <button type="submit" class="px-5 py-3 rounded-lg bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">Salvar Alterações</button>
        </div>
    </form>
</div>
@endsection
