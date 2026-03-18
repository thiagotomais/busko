@extends('layouts.app')

@section('title', 'Login - Busko')

@section('content')
<div class="bg-white rounded-lg shadow-xl p-8">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-blue-600">Busko</h1>
        <p class="text-gray-600 mt-2">Sistema de Gestão</p>
    </div>

    <form action="{{ route('portal.login.store') }}" method="POST">
        @csrf

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
            <input 
                type="email" 
                name="email" 
                id="email" 
                placeholder="seu@email.com"
                value="{{ old('email') }}"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                required
            >
            @error('email')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="block text-gray-700 font-bold mb-2">Senha</label>
            <input 
                type="password" 
                name="password" 
                id="password" 
                placeholder="••••••••"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                required
            >
            @error('password')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- User Type -->
        <div class="mb-6">
            <label for="type" class="block text-gray-700 font-bold mb-2">Tipo de Usuário</label>
            <select 
                name="type" 
                id="type" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('type') border-red-500 @enderror"
                required
            >
                <option value="">Selecione um tipo...</option>
                <option value="driver" {{ old('type') === 'driver' ? 'selected' : '' }}>Motorista</option>
                <option value="guardian" {{ old('type') === 'guardian' ? 'selected' : '' }}>Guardião</option>
                <option value="admin" {{ old('type') === 'admin' ? 'selected' : '' }}>Administrador</option>
            </select>
            @error('type')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Submit Button -->
        <button 
            type="submit" 
            class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200"
        >
            Entrar
        </button>
    </form>

    <!-- Help Text -->
    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-sm text-gray-700">
            <strong>Demo:</strong><br>
            Motorista: <code class="bg-white px-2 py-1 rounded">thiago@tomais</code> / <code class="bg-white px-2 py-1 rounded">thiago@tomais</code> (Tipo: Motorista)<br>
            Guardião: <code class="bg-white px-2 py-1 rounded">guardian@test.com</code> / <code class="bg-white px-2 py-1 rounded">password</code> (Tipo: Guardião)<br>
            Admin: <code class="bg-white px-2 py-1 rounded">admin@busko.com</code> / <code class="bg-white px-2 py-1 rounded">admin@busko</code> (Tipo: Administrador)
        </p>
    </div>
</div>
@endsection
