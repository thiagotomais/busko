@extends('layouts.app')

@section('title', 'Selecionar Empresa - Financeiro')

@section('page-title', 'Financeiro')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800">Selecione uma empresa para continuar</h3>
        <p class="text-sm text-gray-600 mt-1">
            Como administrador global, escolha a empresa que deseja gerenciar no financeiro.
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Empresa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Motoristas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Guardiões</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Usuários</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($tenants as $tenant)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $tenant->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $tenant->drivers_count }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $tenant->guardians_count }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $tenant->users_count }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('portal.financial.index', ['company' => $tenant->uid]) }}" class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition font-medium">
                                Abrir financeiro
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Nenhuma empresa disponível.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
