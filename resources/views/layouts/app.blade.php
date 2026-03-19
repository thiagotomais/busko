<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Busko - Sistema de Gestão')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        @if(auth()->check())
            <!-- Dashboard Layout -->
            <div class="flex h-screen bg-gray-100">
                <!-- Sidebar -->
                <div class="w-64 bg-white shadow">
                    <div class="px-6 py-8">
                        <h1 class="text-2xl font-bold text-blue-600">Busko</h1>
                        <p class="text-xs text-gray-500 mt-1">Sistema de Gestão</p>
                    </div>

                    <nav class="mt-6 ml-4">
                        <a href="{{ route('portal.dashboard') }}" class="nav-link {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                            <span class="text-xl">📊</span> Dashboard
                        </a> <br />
                        
                        <a href="{{ route('portal.drivers.index') }}" class="nav-link {{ request()->routeIs('portal.drivers.*') ? 'active' : '' }}">
                            <span class="text-xl">🚗</span> Motoristas
                        </a> <br />
                        
                        <a href="{{ route('portal.guardians.index') }}" class="nav-link {{ request()->routeIs('portal.guardians.*') ? 'active' : '' }}">
                            <span class="text-xl">👥</span> Guardiões
                        </a> <br />

                        @if(auth()->user()?->type === \App\Enums\UserType::ADMIN || (auth()->user()?->type === \App\Enums\UserType::DRIVER && auth()->user()?->is_company_manager))
                            <a href="{{ route('portal.passengers.index') }}" class="nav-link {{ request()->routeIs('portal.passengers.*') ? 'active' : '' }}">
                                <span class="text-xl">🚌</span> Passageiros
                            </a> <br />
                        @endif

                        <a href="{{ route('portal.company.index') }}" class="nav-link {{ request()->routeIs('portal.company.*') ? 'active' : '' }}">
                            <span class="text-xl">🏢</span> Empresa
                        </a> <br />

                        @if(auth()->user()?->type === \App\Enums\UserType::ADMIN || (auth()->user()?->type === \App\Enums\UserType::DRIVER && auth()->user()?->is_company_manager))
                            <a href="{{ route('portal.users.index') }}" class="nav-link {{ request()->routeIs('portal.users.*') ? 'active' : '' }}">
                                <span class="text-xl">🧾</span> Gestão de Usuários
                            </a> <br />
                        @endif

                        <hr class="my-4">

                        <div class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase">
                            {{ auth()->user()->name }}
                        </div>
                        
                        <form action="{{ route('portal.logout') }}" method="POST" class="px-6">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 rounded hover:bg-red-50 text-red-600 font-medium text-sm">
                                Sair
                            </button>
                        </form>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="flex-1 flex flex-col overflow-hidden">
                    <!-- Header -->
                    <div class="bg-white shadow">
                        <div class="px-6 py-4 flex justify-between items-center">
                            <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h2>
                            <div class="text-sm text-gray-600">
                                {{ now()->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 overflow-auto">
                        <div class="p-6">
                            @if(session('success'))
                                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Auth Layout -->
            <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600">
                <div class="w-full max-w-md">
                    @yield('content')
                </div>
            </div>
        @endif
    </body>
</html>

<style>
    .nav-link {
        @apply block w-full flex items-center gap-3 px-6 py-4 mb-2 text-gray-700 hover:bg-gray-100 transition;
    }

    .nav-link.active {
        @apply bg-blue-50 text-blue-600 border-r-4 border-blue-600;
    }
</style>
