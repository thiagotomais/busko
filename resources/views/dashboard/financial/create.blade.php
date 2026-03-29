@extends('layouts.app')

@section('title', 'Nova Mensalidade - Busko')

@section('page-title', 'Nova Mensalidade')

@section('content')
@php($companyParams = request()->filled('company') ? ['company' => (string) request()->query('company')] : (request()->filled('company_id') ? ['company_id' => request()->integer('company_id')] : []))
<div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
    <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800">Cadastrar Mensalidade</h3>
        <p class="text-sm text-gray-600 mt-1">Crie um lançamento mensal por passageiro. A competência não pode se repetir para o mesmo passageiro.</p>
    </div>

    <form action="{{ route('portal.financial.store', $companyParams) }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label for="passenger_id" class="block text-sm font-semibold text-gray-700 mb-2">Passageiro</label>
            <select id="passenger_id" name="passenger_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('passenger_id') border-red-500 @enderror" required>
                <option value="">Selecione...</option>
                @foreach($passengers as $passenger)
                    <option value="{{ $passenger->id }}" @selected((int) old('passenger_id') === (int) $passenger->id)>
                        {{ $passenger->name }} · Guardião: {{ $passenger->guardian?->user?->name ?? '-' }}
                    </option>
                @endforeach
            </select>
            @error('passenger_id')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="competence_month" class="block text-sm font-semibold text-gray-700 mb-2">Competência</label>
                <input type="month" id="competence_month" name="competence_month" value="{{ old('competence_month', now()->format('Y-m')) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('competence_month') border-red-500 @enderror" required>
                @error('competence_month')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="due_date" class="block text-sm font-semibold text-gray-700 mb-2">Vencimento</label>
                <input type="date" id="due_date" name="due_date" value="{{ old('due_date', now()->addDays(10)->format('Y-m-d')) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('due_date') border-red-500 @enderror" required>
                @error('due_date')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div>
            <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">Valor (R$)</label>
            <input type="number" id="amount" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" placeholder="0,00" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('amount') border-red-500 @enderror" required>
            @error('amount')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Observações</label>
            <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
            @error('notes')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('portal.financial.index', $companyParams) }}" class="px-5 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Cancelar</a>
            <button type="submit" class="px-5 py-3 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">Salvar Mensalidade</button>
        </div>
    </form>
</div>
@endsection
