@extends('layouts.app')

@section('title', 'Cadastro de Usuário - Busko')

@section('page-title', 'Cadastro de Usuário')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6">
    <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800">Novo Usuário</h3>
        <p class="text-sm text-gray-600 mt-1">Cadastre usuários da empresa e, para motoristas, opcionalmente marque como Gestor Empresa.</p>
    </div>

    <form action="{{ route('portal.users.store') }}" method="POST" class="space-y-5">
        @csrf

        @if(request()->filled('company_id'))
            <input type="hidden" name="company_id" value="{{ request()->integer('company_id') }}">
        @endif

        <div>
            <label for="type" class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Usuário</label>
            <select id="type" name="type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('type') border-red-500 @enderror" required>
                <option value="">Selecione...</option>
                <option value="driver" {{ old('type') === 'driver' ? 'selected' : '' }}>Motorista</option>
                <option value="guardian" {{ old('type') === 'guardian' ? 'selected' : '' }}>Guardião</option>
                @if($canCreateAdmin)
                    <option value="admin" {{ old('type') === 'admin' ? 'selected' : '' }}>Administrador</option>
                @endif
            </select>
            @error('type')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div id="driver-manager-field" class="p-4 rounded-lg border border-amber-200 bg-amber-50">
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-amber-900">
                <input type="checkbox" id="is_company_manager" name="is_company_manager" value="1" {{ old('is_company_manager') ? 'checked' : '' }} class="rounded border-amber-400 text-amber-600 focus:ring-amber-500">
                Marcar como Gestor Empresa
            </label>
            <p class="text-xs text-amber-800 mt-2">Gestor Empresa pode cadastrar usuários Motorista e Guardião da própria empresa.</p>
            @error('is_company_manager')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nome</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" required>
                @error('name')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror" required>
                @error('email')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Senha</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror" required>
                @error('password')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirmar Senha</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
        </div>

        <div id="person-fields" class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="cpf" class="block text-sm font-semibold text-gray-700 mb-2">CPF</label>
                <input type="text" id="cpf" name="cpf" value="{{ old('cpf') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('cpf') border-red-500 @enderror" placeholder="Somente números ou formatado">
                @error('cpf')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div id="driver-cnh-field">
                <label for="cnh" class="block text-sm font-semibold text-gray-700 mb-2">CNH</label>
                <input type="text" id="cnh" name="cnh" value="{{ old('cnh') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('cnh') border-red-500 @enderror" placeholder="11 dígitos">
                @error('cnh')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div id="guardian-driver-field">
            <label for="primary_driver_id" class="block text-sm font-semibold text-gray-700 mb-2">Motorista Principal (opcional)</label>
            <select id="primary_driver_id" name="primary_driver_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('primary_driver_id') border-red-500 @enderror">
                <option value="">Nenhum</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" {{ (string) old('primary_driver_id') === (string) $driver->id ? 'selected' : '' }}>
                        {{ $driver->user?->name ?? 'Motorista #' . $driver->id }} - CPF {{ $driver->cpf }}
                    </option>
                @endforeach
            </select>
            @error('primary_driver_id')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
            <p class="text-xs text-gray-500 mt-2">O guardião pertence à empresa e pode ser vinculado ao motorista principal.</p>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('portal.users.index', request()->filled('company_id') ? ['company_id' => request()->integer('company_id')] : []) }}" class="px-5 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Cancelar</a>
            <button type="submit" class="px-5 py-3 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">Cadastrar Usuário</button>
        </div>
    </form>
</div>

<script>
    function toggleTypeFields() {
        const type = document.getElementById('type').value;
        const personFields = document.getElementById('person-fields');
        const cnhField = document.getElementById('driver-cnh-field');
        const guardianDriverField = document.getElementById('guardian-driver-field');
        const driverManagerField = document.getElementById('driver-manager-field');
        const managerInput = document.getElementById('is_company_manager');

        const showPerson = type === 'driver' || type === 'guardian';
        personFields.style.display = showPerson ? '' : 'none';

        cnhField.style.display = type === 'driver' ? '' : 'none';
        guardianDriverField.style.display = type === 'guardian' ? '' : 'none';
        driverManagerField.style.display = type === 'driver' ? '' : 'none';

        if (type !== 'driver') {
            managerInput.checked = false;
        }
    }

    document.getElementById('type').addEventListener('change', toggleTypeFields);
    toggleTypeFields();
</script>
@endsection