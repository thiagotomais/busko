@extends('layouts.app')

@section('title', 'Passageiros - Busko')

@section('page-title', 'Passageiros')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">Lista de Passageiros</h3>
            <p class="text-sm text-gray-600 mt-1">Passageiros sempre vinculados ao guardião responsável financeiro.</p>
        </div>
        <a href="{{ route('portal.passengers.create', request()->filled('company_id') ? ['company_id' => request()->integer('company_id')] : []) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            + Novo Passageiro
        </a>
    </div>

    @if($passengers->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passageiro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guardião</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serviço</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horários</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($passengers as $passenger)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-900">{{ $passenger->name }}</p>
                                <p class="text-xs text-gray-500">RG: {{ $passenger->rg }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $passenger->guardian?->user?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                @if($passenger->service_type === 'ida')
                                    Ida
                                @elseif($passenger->service_type === 'volta')
                                    Volta
                                @else
                                    Ida e Volta
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 capitalize">{{ $passenger->period }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ substr($passenger->entry_time, 0, 5) }} / {{ substr($passenger->exit_time, 0, 5) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-gray-200">
            {{ $passengers->links() }}
        </div>
    @else
        <div class="p-10 text-center">
            <p class="text-gray-500 mb-4">Nenhum passageiro cadastrado ainda.</p>
            <a href="{{ route('portal.passengers.create', request()->filled('company_id') ? ['company_id' => request()->integer('company_id')] : []) }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Cadastrar Primeiro Passageiro
            </a>
        </div>
    @endif
</div>
@endsection
