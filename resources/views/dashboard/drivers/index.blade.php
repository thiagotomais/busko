@extends('layouts.app')

@section('title', 'Motoristas - Busko')

@section('page-title', 'Motoristas')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Lista de Motoristas</h3>
        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
            {{ $drivers->total() }} total
        </span>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">CPF</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">CNH</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($drivers as $driver)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $driver->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            {{ $driver->user->email ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <code class="bg-gray-100 px-2 py-1 rounded">{{ $driver->cpf }}</code>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <code class="bg-gray-100 px-2 py-1 rounded">{{ $driver->cnh }}</code>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('portal.drivers.show', $driver) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                Ver Detalhes →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            Nenhum motorista encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($drivers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $drivers->links() }}
        </div>
    @endif
</div>
@endsection
