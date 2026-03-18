@extends('layouts.app')

@section('title', 'Editar Usuário - Busko')

@section('page-title', 'Editar Usuário')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow p-6">
    <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800">{{ $user->name }}</h3>
        <p class="text-sm text-gray-600 mt-1">Tipo: <span class="capitalize font-medium">{{ $user->type?->value ?? $user->type }}</span></p>
    </div>

    <form action="{{ route('portal.users.update', $user) }}" method="POST" class="space-y-5">
        @csrf
        @method('PATCH')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nome</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" required>
                @error('name')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror" required>
                @error('email')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        @if(($user->type?->value ?? $user->type) === 'driver' || ($user->type?->value ?? $user->type) === 'guardian')
            <div>
                <label for="cpf" class="block text-sm font-semibold text-gray-700 mb-2">CPF</label>
                <input type="text" id="cpf" name="cpf" value="{{ old('cpf', $user->driver?->cpf ?? $user->guardian?->cpf) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('cpf') border-red-500 @enderror" required>
                @error('cpf')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        @endif

        @if(($user->type?->value ?? $user->type) === 'driver')
            <div>
                <label for="cnh" class="block text-sm font-semibold text-gray-700 mb-2">CNH</label>
                <input type="text" id="cnh" name="cnh" value="{{ old('cnh', $user->driver?->cnh) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('cnh') border-red-500 @enderror" required>
                @error('cnh')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        @endif

        @if(($user->type?->value ?? $user->type) === 'guardian')
            <div>
                <label for="primary_driver_id" class="block text-sm font-semibold text-gray-700 mb-2">Motorista Principal (opcional)</label>
                <select id="primary_driver_id" name="primary_driver_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('primary_driver_id') border-red-500 @enderror">
                    <option value="">Nenhum</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ (string) old('primary_driver_id', $user->guardian?->primary_driver_id) === (string) $driver->id ? 'selected' : '' }}>
                            {{ $driver->user?->name ?? 'Motorista #' . $driver->id }} - CPF {{ $driver->cpf }}
                        </option>
                    @endforeach
                </select>
                @error('primary_driver_id')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Nova Senha (opcional)</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                @error('password')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirmar Nova Senha</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('portal.users.index') }}" class="px-5 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Cancelar</a>
            <button type="submit" class="px-5 py-3 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">Salvar Alterações</button>
        </div>
    </form>
</div>
@endsection
