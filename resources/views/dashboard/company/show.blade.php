@extends('layouts.app')

@section('title', 'Empresa - Busko')

@section('page-title', 'Empresa de Transportes')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
                <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase mb-1">Gestão da Empresa</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $tenant->name }}</h3>
                    <p class="text-sm text-gray-600 mt-2">Atualize os dados cadastrais da empresa de transportes vinculada ao seu acesso.</p>
                </div>
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $tenant->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $tenant->is_active ? 'Ativa' : 'Inativa' }}
                </span>
            </div>

            <form action="{{ route('portal.company.update') }}" method="POST" class="space-y-5">
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
                    <p class="text-xs text-gray-500 mt-2">Use apenas letras, números e hífens para identificar a empresa.</p>
                    @error('slug')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-5 py-3 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 transition">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h4 class="text-sm font-semibold text-gray-700 uppercase mb-4">Resumo</h4>
            <div class="space-y-4 text-sm">
                <div>
                    <p class="text-gray-500">Slug Atual</p>
                    <p class="font-medium"><code class="bg-gray-100 px-2 py-1 rounded">{{ $tenant->slug }}</code></p>
                </div>
                <div>
                    <p class="text-gray-500">Motoristas</p>
                    <p class="font-medium">{{ $tenant->drivers_count }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Guardiões</p>
                    <p class="font-medium">{{ $tenant->guardians_count }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Endereços</p>
                    <p class="font-medium">{{ $tenant->addresses_count }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Criada em</p>
                    <p class="font-medium">{{ $tenant->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Atualizada em</p>
                    <p class="font-medium">{{ $tenant->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 uppercase mb-3">Status Operacional</h4>
            <p class="text-sm text-gray-600 mb-4">Ao desativar a empresa, novos logins vinculados a ela deixam de ser autorizados até a reativação.</p>

            <form action="{{ route('portal.company.toggle-status') }}" method="POST">
                @csrf
                <button type="submit" class="w-full px-4 py-3 rounded-lg font-semibold transition {{ $tenant->is_active ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-green-600 text-white hover:bg-green-700' }}">
                    {{ $tenant->is_active ? 'Desativar Empresa' : 'Reativar Empresa' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection