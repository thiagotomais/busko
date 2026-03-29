@extends('layouts.app')

@php
    $isEditing = $transportRoute->exists;
    $companyParams = request()->filled('company')
        ? ['company' => (string) request()->query('company')]
        : (request()->filled('company_id') ? ['company_id' => request()->integer('company_id')] : []);
    $formAction = $isEditing
        ? route('portal.transport-routes.update', array_merge(['transportRoute' => $transportRoute], $companyParams))
        : route('portal.transport-routes.store', $companyParams);
    $selectedPassengerIds = collect(old('passenger_ids', $isEditing ? $transportRoute->passengers->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $stopOrders = old('stop_orders', $isEditing ? $transportRoute->passengers->pluck('pivot.stop_order', 'id')->all() : []);
    $weekdayLabels = \App\Models\TransportRoute::weekdayLabels();
@endphp

@section('title', ($isEditing ? 'Editar' : 'Cadastrar') . ' Rota - Busko')

@section('page-title', ($isEditing ? 'Editar' : 'Cadastrar') . ' Rota')

@section('content')
<div class="max-w-6xl mx-auto bg-white rounded-lg shadow p-6">
    <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800">{{ $isEditing ? 'Editar Rota' : 'Nova Rota' }}</h3>
        <p class="text-sm text-gray-600 mt-1">Defina motorista, dias de operação e passageiros com ordem de parada.</p>
    </div>

    <form action="{{ $formAction }}" method="POST" class="space-y-6">
        @csrf
        @if($isEditing)
            @method('PATCH')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nome da Rota</label>
                <input type="text" id="name" name="name" value="{{ old('name', $transportRoute->name) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" required>
                @error('name')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="driver_id" class="block text-sm font-semibold text-gray-700 mb-2">Motorista</label>
                <select id="driver_id" name="driver_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('driver_id') border-red-500 @enderror" required>
                    <option value="">Selecione...</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" @selected((int) old('driver_id', $transportRoute->driver_id) === (int) $driver->id)>
                            {{ $driver->user?->name ?? 'Motorista sem usuário' }}
                        </option>
                    @endforeach
                </select>
                @error('driver_id')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="direction" class="block text-sm font-semibold text-gray-700 mb-2">Direção</label>
                <select id="direction" name="direction" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('direction') border-red-500 @enderror" required>
                    <option value="ida" @selected(old('direction', $transportRoute->direction) === 'ida')>Ida</option>
                    <option value="volta" @selected(old('direction', $transportRoute->direction) === 'volta')>Volta</option>
                </select>
                @error('direction')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="period" class="block text-sm font-semibold text-gray-700 mb-2">Período</label>
                <select id="period" name="period" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('period') border-red-500 @enderror" required>
                    <option value="manha" @selected(old('period', $transportRoute->period) === 'manha')>Manhã</option>
                    <option value="tarde" @selected(old('period', $transportRoute->period) === 'tarde')>Tarde</option>
                    <option value="noite" @selected(old('period', $transportRoute->period) === 'noite')>Noite</option>
                </select>
                @error('period')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="vehicle_name" class="block text-sm font-semibold text-gray-700 mb-2">Veículo</label>
                <input type="text" id="vehicle_name" name="vehicle_name" value="{{ old('vehicle_name', $transportRoute->vehicle_name) }}" placeholder="Ex: Van Escolar 01" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('vehicle_name') border-red-500 @enderror">
                @error('vehicle_name')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="vehicle_plate" class="block text-sm font-semibold text-gray-700 mb-2">Placa</label>
                <input type="text" id="vehicle_plate" name="vehicle_plate" value="{{ old('vehicle_plate', $transportRoute->vehicle_plate) }}" placeholder="ABC-1234" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('vehicle_plate') border-red-500 @enderror">
                @error('vehicle_plate')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="border rounded-lg p-4">
            <h4 class="text-sm font-semibold text-gray-700 uppercase mb-4">Dias da Semana</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                @foreach($weekdayLabels as $field => $label)
                    <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $transportRoute->{$field}))>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            @error('weekdays')
                <span class="text-red-600 text-sm mt-2 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Observações</label>
            <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('notes') border-red-500 @enderror">{{ old('notes', $transportRoute->notes) }}</textarea>
            @error('notes')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center gap-3">
            <input type="hidden" name="is_active" value="0">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $transportRoute->is_active))>
                <span class="text-sm font-medium text-gray-700">Rota ativa</span>
            </label>
        </div>

        <div class="border rounded-lg p-4">
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-700 uppercase">Passageiros da Rota</h4>
                <p class="text-sm text-gray-600 mt-1">Selecione os passageiros e informe a ordem de parada. O sistema valida compatibilidade com direção e período ao salvar.</p>
            </div>

            @error('passenger_ids')
                <span class="text-red-600 text-sm mb-3 block">{{ $message }}</span>
            @enderror

            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selecionar</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Passageiro</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guardião</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serviço</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordem</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($passengers as $passenger)
                            @php($isChecked = in_array($passenger->id, $selectedPassengerIds, true))
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    <input type="checkbox" name="passenger_ids[]" value="{{ $passenger->id }}" @checked($isChecked)>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    <p class="font-medium text-gray-900">{{ $passenger->name }}</p>
                                    <p class="text-xs text-gray-500">RG: {{ $passenger->rg }}</p>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $passenger->guardian?->user?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $passenger->service_type === 'ida_volta' ? 'Ida e Volta' : ucfirst($passenger->service_type) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 capitalize">{{ $passenger->period }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    <input type="number" min="1" name="stop_orders[{{ $passenger->id }}]" value="{{ $stopOrders[$passenger->id] ?? '' }}" class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('stop_orders.' . $passenger->id) border-red-500 @enderror">
                                    @error('stop_orders.' . $passenger->id)
                                        <span class="text-red-600 text-xs mt-1 block">{{ $message }}</span>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('portal.transport-routes.index', $companyParams) }}" class="px-5 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Cancelar</a>
            <button type="submit" class="px-5 py-3 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">{{ $isEditing ? 'Salvar Alterações' : 'Cadastrar Rota' }}</button>
        </div>
    </form>
</div>
@endsection