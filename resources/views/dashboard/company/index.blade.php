@extends('layouts.app')

@section('title', 'Empresas - Busko')

@section('page-title', 'Empresas de Transportes')

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Empresas Cadastradas</h3>
            <span class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-sm font-medium inline-block mt-2">
                {{ $tenants->total() }} total
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Empresa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Motoristas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Guardiões</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Usuários</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($tenants as $tenant)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $tenant->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700"><code class="bg-gray-100 px-2 py-1 rounded">{{ $tenant->slug }}</code></td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $tenant->drivers_count }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $tenant->guardians_count }}</td>
                        <td class="px-6 py-4 text-sm text-gray-700">{{ $tenant->users_count }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $tenant->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $tenant->is_active ? 'Ativa' : 'Inativa' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('portal.company.edit', $tenant) }}" class="text-blue-600 hover:text-blue-800 font-medium">Editar</a>
                                <form action="{{ route('portal.company.admin-toggle-status', $tenant) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="font-medium {{ $tenant->is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                        {{ $tenant->is_active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">Nenhuma empresa encontrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tenants->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $tenants->links() }}
        </div>
    @endif
</div>
@endsection
