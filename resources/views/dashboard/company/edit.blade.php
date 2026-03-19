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

        <!-- Banking Information Section -->
        <div class="border-t pt-6 mt-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">Dados de Recebimento</h4>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="bank_code" class="block text-sm font-semibold text-gray-700 mb-2">Código do Banco</label>
                    <input type="text" id="bank_code" name="bank_code" placeholder="001" value="{{ old('bank_code', $tenant->bank_code) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 @error('bank_code') border-red-500 @enderror">
                    @error('bank_code')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Ex: 001 (Banco do Brasil), 033 (Santander), 104 (Caixa)</p>
                </div>

                <div>
                    <label for="bank_branch" class="block text-sm font-semibold text-gray-700 mb-2">Agência</label>
                    <input type="text" id="bank_branch" name="bank_branch" placeholder="0001" value="{{ old('bank_branch', $tenant->bank_branch) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 @error('bank_branch') border-red-500 @enderror">
                    @error('bank_branch')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="bank_account" class="block text-sm font-semibold text-gray-700 mb-2">Número da Conta</label>
                    <input type="text" id="bank_account" name="bank_account" placeholder="123456-7" value="{{ old('bank_account', $tenant->bank_account) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 @error('bank_account') border-red-500 @enderror">
                    @error('bank_account')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="bank_account_type" class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Conta</label>
                    <select id="bank_account_type" name="bank_account_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 @error('bank_account_type') border-red-500 @enderror">
                        <option value="">Selecione...</option>
                        <option value="corrente" @selected(old('bank_account_type', $tenant->bank_account_type) === 'corrente')>Conta Corrente</option>
                        <option value="poupanca" @selected(old('bank_account_type', $tenant->bank_account_type) === 'poupanca')>Conta Poupança</option>
                    </select>
                    @error('bank_account_type')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div>
                <label for="pix_key" class="block text-sm font-semibold text-gray-700 mb-2">Chave Pix</label>
                <input type="text" id="pix_key" name="pix_key" placeholder="seu.email@empresa.com ou 123.456.789-00" value="{{ old('pix_key', $tenant->pix_key) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-amber-500 @error('pix_key') border-red-500 @enderror">
                @error('pix_key')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Pode ser CPF, email, telefone ou chave aleatória</p>
            </div>
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
