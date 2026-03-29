@extends('layouts.app')

@section('title', 'Guardiões - Busko')

@section('page-title', 'Guardiões')

@section('content')
@php
    $companyParams = request()->filled('company')
        ? ['company' => (string) request()->query('company')]
        : (request()->filled('company_id') ? ['company_id' => request()->integer('company_id')] : []);
@endphp
<div class="bg-white rounded-lg shadow overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-800">Lista de Guardiões</h3>
        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
            {{ $guardians->total() }} total
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Motoristas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($guardians as $guardian)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $guardian->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            {{ $guardian->user->email ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <code class="bg-gray-100 px-2 py-1 rounded">{{ $guardian->cpf }}</code>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                                {{ $guardian->drivers->count() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if(!empty($guardian->slug))
                                <div class="flex items-center gap-4">
                                    <a href="{{ route('portal.guardians.show', array_merge(['guardian' => $guardian], $companyParams)) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                        Ver Detalhes →
                                    </a>
                                    @if(auth()->user()?->type === \App\Enums\UserType::ADMIN || (auth()->user()?->type === \App\Enums\UserType::DRIVER && auth()->user()?->is_company_manager))
                                        <a href="{{ route('portal.passengers.create', array_merge(['guardian_id' => $guardian->id], $companyParams)) }}" class="text-green-600 hover:text-green-800 font-medium">
                                            Cadastrar Passageiro
                                        </a>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-500">Sem slug</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            Nenhum guardião encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($guardians->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $guardians->links() }}
        </div>
    @endif
</div>
@endsection
