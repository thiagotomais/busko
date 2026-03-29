@extends('layouts.app')

@php
    $passenger = $passenger ?? new \App\Models\Passenger();
    $isEditing = $passenger->exists;
    $companyParams = request()->filled('company')
        ? ['company' => (string) request()->query('company')]
        : (request()->filled('company_id') ? ['company_id' => request()->integer('company_id')] : []);
    $formAction = $isEditing
        ? route('portal.passengers.update', array_merge(['passenger' => $passenger], $companyParams))
        : route('portal.passengers.store', $companyParams);
    $assignedIdaRouteId = old('ida_route_id', $passenger->assignedRouteFor('ida')?->id);
    $assignedVoltaRouteId = old('volta_route_id', $passenger->assignedRouteFor('volta')?->id);
@endphp

@section('title', ($isEditing ? 'Editar' : 'Cadastrar') . ' Passageiro - Busko')

@section('page-title', ($isEditing ? 'Editar' : 'Cadastrar') . ' Passageiro')

@section('content')
<div class="max-w-5xl mx-auto bg-white rounded-lg shadow p-6">
    <div class="mb-6">
        <h3 class="text-xl font-semibold text-gray-800">{{ $isEditing ? 'Editar Passageiro' : 'Novo Passageiro' }}</h3>
        <p class="text-sm text-gray-600 mt-1">Todo passageiro deve estar vinculado a um guardião responsável financeiro.</p>
    </div>

    <form action="{{ $formAction }}" method="POST" class="space-y-6">
        @csrf
        @if($isEditing)
            @method('PATCH')
        @endif

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Guardião Responsável Financeiro</label>
            <input type="hidden" id="guardian_id" name="guardian_id" value="{{ old('guardian_id', $guardian->id) }}">
            <div class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                <p class="font-medium text-gray-900">{{ $guardian->user?->name ?? 'Guardião sem usuário' }}</p>
                <p class="text-sm text-gray-600 mt-1">Documento: CPF {{ $guardian->cpf }}</p>
            </div>
            @error('guardian_id')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="border rounded-lg p-4">
            <h4 class="text-sm font-semibold text-gray-700 uppercase mb-4">Serviço Contratado</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="service_type" value="ida" @checked(old('service_type', $passenger->service_type ?? 'ida_volta') === 'ida') required>
                    <span>Ida</span>
                </label>
                <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="service_type" value="volta" @checked(old('service_type', $passenger->service_type ?? 'ida_volta') === 'volta') required>
                    <span>Volta</span>
                </label>
                <label class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                    <input type="radio" name="service_type" value="ida_volta" @checked(old('service_type', $passenger->service_type ?? 'ida_volta') === 'ida_volta') required>
                    <span>Ida e Volta</span>
                </label>
            </div>
            @error('service_type')
                <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nome do Passageiro</label>
                <input type="text" id="name" name="name" value="{{ old('name', $passenger->name ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" required>
                @error('name')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="birth_date" class="block text-sm font-semibold text-gray-700 mb-2">Data de Nascimento</label>
                <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', $passenger->birth_date?->format('Y-m-d')) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('birth_date') border-red-500 @enderror" required>
                @error('birth_date')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="school_grade" class="block text-sm font-semibold text-gray-700 mb-2">Série Atual</label>
                <input type="text" id="school_grade" name="school_grade" value="{{ old('school_grade', $passenger->school_grade ?? '') }}" placeholder="Ex: 5º ano" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('school_grade') border-red-500 @enderror" required>
                @error('school_grade')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="period" class="block text-sm font-semibold text-gray-700 mb-2">Período</label>
                <select id="period" name="period" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('period') border-red-500 @enderror" required>
                    <option value="manha" @selected(old('period', $passenger->period ?? 'tarde') === 'manha')>Manhã</option>
                    <option value="tarde" @selected(old('period', $passenger->period ?? 'tarde') === 'tarde')>Tarde</option>
                    <option value="noite" @selected(old('period', $passenger->period ?? 'tarde') === 'noite')>Noite</option>
                </select>
                @error('period')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="rg" class="block text-sm font-semibold text-gray-700 mb-2">RG</label>
                <input type="text" id="rg" name="rg" value="{{ old('rg', $passenger->rg ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('rg') border-red-500 @enderror" required>
                @error('rg')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="monthly_fee" class="block text-sm font-semibold text-gray-700 mb-2">Mensalidade Padrão (R$)</label>
                <input type="number" id="monthly_fee" name="monthly_fee" value="{{ old('monthly_fee', $passenger->monthly_fee) }}" step="0.01" min="0.01" placeholder="Ex: 450.00" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('monthly_fee') border-red-500 @enderror">
                @error('monthly_fee')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Este valor será usado automaticamente na geração de mensalidades em lote.</p>
            </div>
        </div>

        <div class="border rounded-lg p-4 space-y-4">
            <div>
                <h4 class="text-sm font-semibold text-gray-700 uppercase">Rotas Vinculadas</h4>
                <p class="text-sm text-gray-600 mt-1">Defina separadamente a rota de ida e a rota de volta. A ordem das paradas continua sendo organizada na tela de rotas.</p>
            </div>

            <div id="ida_route_section" data-route-direction-section="ida" class="grid grid-cols-1 gap-2">
                <label for="ida_route_id" class="block text-sm font-semibold text-gray-700">Rota de Ida</label>
                <select id="ida_route_id" name="ida_route_id" data-route-select="ida" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('ida_route_id') border-red-500 @enderror">
                    <option value="">Selecione uma rota de ida</option>
                    @foreach($idaRoutes as $route)
                        <option
                            value="{{ $route->id }}"
                            data-period="{{ $route->period }}"
                            @selected((string) $assignedIdaRouteId === (string) $route->id)
                        >
                            {{ $route->name }} · {{ ucfirst($route->period) }} · {{ $route->driver?->user?->name ?? 'Sem motorista' }}{{ $route->is_active ? '' : ' · Inativa' }}
                        </option>
                    @endforeach
                </select>
                @error('ida_route_id')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
                <p class="text-xs text-gray-500" data-route-empty-message="ida">Nenhuma rota de ida disponível para o período selecionado.</p>
            </div>

            <div id="volta_route_section" data-route-direction-section="volta" class="grid grid-cols-1 gap-2">
                <label for="volta_route_id" class="block text-sm font-semibold text-gray-700">Rota de Volta</label>
                <select id="volta_route_id" name="volta_route_id" data-route-select="volta" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('volta_route_id') border-red-500 @enderror">
                    <option value="">Selecione uma rota de volta</option>
                    @foreach($voltaRoutes as $route)
                        <option
                            value="{{ $route->id }}"
                            data-period="{{ $route->period }}"
                            @selected((string) $assignedVoltaRouteId === (string) $route->id)
                        >
                            {{ $route->name }} · {{ ucfirst($route->period) }} · {{ $route->driver?->user?->name ?? 'Sem motorista' }}{{ $route->is_active ? '' : ' · Inativa' }}
                        </option>
                    @endforeach
                </select>
                @error('volta_route_id')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
                <p class="text-xs text-gray-500" data-route-empty-message="volta">Nenhuma rota de volta disponível para o período selecionado.</p>
            </div>
        </div>

        <div class="border rounded-lg p-4">
            <h4 class="text-sm font-semibold text-gray-700 uppercase mb-4">Endereço Residencial</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="residential_zip" class="block text-sm font-semibold text-gray-700 mb-2">CEP</label>
                    <div class="flex gap-2">
                        <input type="text" id="residential_zip" name="residential_zip" value="{{ old('residential_zip', $passenger->residential_zip ?? '') }}" placeholder="00000-000" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('residential_zip') border-red-500 @enderror" required>
                        <button type="button" id="btn_search_residential_zip" class="px-4 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Buscar</button>
                    </div>
                    @error('residential_zip')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                    <p id="residential_zip_feedback" class="text-xs text-gray-500 mt-1"></p>
                </div>

                <div class="md:col-span-2">
                    <label for="residential_street" class="block text-sm font-semibold text-gray-700 mb-2">Logradouro</label>
                    <input type="text" id="residential_street" name="residential_street" value="{{ old('residential_street', $passenger->residential_street ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('residential_street') border-red-500 @enderror" required>
                    @error('residential_street')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="residential_number" class="block text-sm font-semibold text-gray-700 mb-2">Número</label>
                    <input type="text" id="residential_number" name="residential_number" value="{{ old('residential_number', $passenger->residential_number ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('residential_number') border-red-500 @enderror" required>
                    @error('residential_number')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="residential_complement" class="block text-sm font-semibold text-gray-700 mb-2">Complemento</label>
                    <input type="text" id="residential_complement" name="residential_complement" value="{{ old('residential_complement', $passenger->residential_complement ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('residential_complement') border-red-500 @enderror">
                    @error('residential_complement')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="residential_neighborhood" class="block text-sm font-semibold text-gray-700 mb-2">Bairro</label>
                    <input type="text" id="residential_neighborhood" name="residential_neighborhood" value="{{ old('residential_neighborhood', $passenger->residential_neighborhood ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('residential_neighborhood') border-red-500 @enderror" required>
                    @error('residential_neighborhood')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="residential_city" class="block text-sm font-semibold text-gray-700 mb-2">Cidade</label>
                    <input type="text" id="residential_city" name="residential_city" value="{{ old('residential_city', $passenger->residential_city ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('residential_city') border-red-500 @enderror" required>
                    @error('residential_city')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="residential_state" class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                    <input type="text" id="residential_state" name="residential_state" value="{{ old('residential_state', $passenger->residential_state ?? '') }}" maxlength="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('residential_state') border-red-500 @enderror" required>
                    @error('residential_state')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Endereço de Retirada --}}
        <div class="border rounded-lg p-4">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-sm font-semibold text-gray-700 uppercase">Endereço de Retirada do Passageiro</h4>
                <button type="button" onclick="copyResidentialTo('pickup')" class="text-xs px-3 py-1.5 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 transition">Copiar Endereço Residencial</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="pickup_zip" class="block text-sm font-semibold text-gray-700 mb-2">CEP</label>
                    <div class="flex gap-2">
                        <input type="text" id="pickup_zip" name="pickup_zip" value="{{ old('pickup_zip', $passenger->pickup_zip ?? '') }}" placeholder="00000-000" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('pickup_zip') border-red-500 @enderror" required>
                        <button type="button" id="btn_search_pickup_zip" class="px-4 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Buscar</button>
                    </div>
                    @error('pickup_zip')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                    <p id="pickup_zip_feedback" class="text-xs text-gray-500 mt-1"></p>
                </div>
                <div class="md:col-span-2">
                    <label for="pickup_street" class="block text-sm font-semibold text-gray-700 mb-2">Logradouro</label>
                    <input type="text" id="pickup_street" name="pickup_street" value="{{ old('pickup_street', $passenger->pickup_street ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('pickup_street') border-red-500 @enderror" required>
                    @error('pickup_street')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="pickup_number" class="block text-sm font-semibold text-gray-700 mb-2">Número</label>
                    <input type="text" id="pickup_number" name="pickup_number" value="{{ old('pickup_number', $passenger->pickup_number ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('pickup_number') border-red-500 @enderror" required>
                    @error('pickup_number')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="pickup_complement" class="block text-sm font-semibold text-gray-700 mb-2">Complemento</label>
                    <input type="text" id="pickup_complement" name="pickup_complement" value="{{ old('pickup_complement', $passenger->pickup_complement ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('pickup_complement') border-red-500 @enderror">
                    @error('pickup_complement')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="pickup_neighborhood" class="block text-sm font-semibold text-gray-700 mb-2">Bairro</label>
                    <input type="text" id="pickup_neighborhood" name="pickup_neighborhood" value="{{ old('pickup_neighborhood', $passenger->pickup_neighborhood ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('pickup_neighborhood') border-red-500 @enderror" required>
                    @error('pickup_neighborhood')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="pickup_city" class="block text-sm font-semibold text-gray-700 mb-2">Cidade</label>
                    <input type="text" id="pickup_city" name="pickup_city" value="{{ old('pickup_city', $passenger->pickup_city ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('pickup_city') border-red-500 @enderror" required>
                    @error('pickup_city')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="pickup_state" class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                    <input type="text" id="pickup_state" name="pickup_state" value="{{ old('pickup_state', $passenger->pickup_state ?? '') }}" maxlength="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('pickup_state') border-red-500 @enderror" required>
                    @error('pickup_state')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Endereço de Entrega --}}
        <div class="border rounded-lg p-4">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-sm font-semibold text-gray-700 uppercase">Endereço de Entrega do Passageiro</h4>
                <button type="button" onclick="copyResidentialTo('dropoff')" class="text-xs px-3 py-1.5 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 transition">Copiar Endereço Residencial</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="dropoff_zip" class="block text-sm font-semibold text-gray-700 mb-2">CEP</label>
                    <div class="flex gap-2">
                        <input type="text" id="dropoff_zip" name="dropoff_zip" value="{{ old('dropoff_zip', $passenger->dropoff_zip ?? '') }}" placeholder="00000-000" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('dropoff_zip') border-red-500 @enderror" required>
                        <button type="button" id="btn_search_dropoff_zip" class="px-4 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Buscar</button>
                    </div>
                    @error('dropoff_zip')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                    <p id="dropoff_zip_feedback" class="text-xs text-gray-500 mt-1"></p>
                </div>
                <div class="md:col-span-2">
                    <label for="dropoff_street" class="block text-sm font-semibold text-gray-700 mb-2">Logradouro</label>
                    <input type="text" id="dropoff_street" name="dropoff_street" value="{{ old('dropoff_street', $passenger->dropoff_street ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('dropoff_street') border-red-500 @enderror" required>
                    @error('dropoff_street')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="dropoff_number" class="block text-sm font-semibold text-gray-700 mb-2">Número</label>
                    <input type="text" id="dropoff_number" name="dropoff_number" value="{{ old('dropoff_number', $passenger->dropoff_number ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('dropoff_number') border-red-500 @enderror" required>
                    @error('dropoff_number')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="dropoff_complement" class="block text-sm font-semibold text-gray-700 mb-2">Complemento</label>
                    <input type="text" id="dropoff_complement" name="dropoff_complement" value="{{ old('dropoff_complement', $passenger->dropoff_complement ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('dropoff_complement') border-red-500 @enderror">
                    @error('dropoff_complement')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="dropoff_neighborhood" class="block text-sm font-semibold text-gray-700 mb-2">Bairro</label>
                    <input type="text" id="dropoff_neighborhood" name="dropoff_neighborhood" value="{{ old('dropoff_neighborhood', $passenger->dropoff_neighborhood ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('dropoff_neighborhood') border-red-500 @enderror" required>
                    @error('dropoff_neighborhood')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="dropoff_city" class="block text-sm font-semibold text-gray-700 mb-2">Cidade</label>
                    <input type="text" id="dropoff_city" name="dropoff_city" value="{{ old('dropoff_city', $passenger->dropoff_city ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('dropoff_city') border-red-500 @enderror" required>
                    @error('dropoff_city')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="dropoff_state" class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                    <input type="text" id="dropoff_state" name="dropoff_state" value="{{ old('dropoff_state', $passenger->dropoff_state ?? '') }}" maxlength="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('dropoff_state') border-red-500 @enderror" required>
                    @error('dropoff_state')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="entry_time" class="block text-sm font-semibold text-gray-700 mb-2">Horário de Entrada</label>
                <input type="time" id="entry_time" name="entry_time" value="{{ old('entry_time', $passenger->entry_time ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('entry_time') border-red-500 @enderror" required>
                @error('entry_time')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="exit_time" class="block text-sm font-semibold text-gray-700 mb-2">Horário de Saída</label>
                <input type="time" id="exit_time" name="exit_time" value="{{ old('exit_time', $passenger->exit_time ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('exit_time') border-red-500 @enderror" required>
                @error('exit_time')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Endereço da Escola --}}
        <div class="border rounded-lg p-4">
            <div class="mb-4">
                <h4 class="text-sm font-semibold text-gray-700 uppercase">Endereço da Escola</h4>
            </div>
            <div class="mb-4">
                <label for="school_name" class="block text-sm font-semibold text-gray-700 mb-2">Nome da Escola</label>
                <input type="text" id="school_name" name="school_name" value="{{ old('school_name', $passenger->school_name ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('school_name') border-red-500 @enderror" required>
                @error('school_name')
                    <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="school_zip" class="block text-sm font-semibold text-gray-700 mb-2">CEP</label>
                    <div class="flex gap-2">
                        <input type="text" id="school_zip" name="school_zip" value="{{ old('school_zip', $passenger->school_zip ?? '') }}" placeholder="00000-000" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('school_zip') border-red-500 @enderror" required>
                        <button type="button" id="btn_search_school_zip" class="px-4 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Buscar</button>
                    </div>
                    @error('school_zip')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                    <p id="school_zip_feedback" class="text-xs text-gray-500 mt-1"></p>
                </div>
                <div class="md:col-span-2">
                    <label for="school_street" class="block text-sm font-semibold text-gray-700 mb-2">Logradouro</label>
                    <input type="text" id="school_street" name="school_street" value="{{ old('school_street', $passenger->school_street ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('school_street') border-red-500 @enderror" required>
                    @error('school_street')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="school_number" class="block text-sm font-semibold text-gray-700 mb-2">Número</label>
                    <input type="text" id="school_number" name="school_number" value="{{ old('school_number', $passenger->school_number ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('school_number') border-red-500 @enderror" required>
                    @error('school_number')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="school_complement" class="block text-sm font-semibold text-gray-700 mb-2">Complemento</label>
                    <input type="text" id="school_complement" name="school_complement" value="{{ old('school_complement', $passenger->school_complement ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('school_complement') border-red-500 @enderror">
                    @error('school_complement')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="school_neighborhood" class="block text-sm font-semibold text-gray-700 mb-2">Bairro</label>
                    <input type="text" id="school_neighborhood" name="school_neighborhood" value="{{ old('school_neighborhood', $passenger->school_neighborhood ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('school_neighborhood') border-red-500 @enderror" required>
                    @error('school_neighborhood')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="school_city" class="block text-sm font-semibold text-gray-700 mb-2">Cidade</label>
                    <input type="text" id="school_city" name="school_city" value="{{ old('school_city', $passenger->school_city ?? '') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('school_city') border-red-500 @enderror" required>
                    @error('school_city')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="school_state" class="block text-sm font-semibold text-gray-700 mb-2">Estado</label>
                    <input type="text" id="school_state" name="school_state" value="{{ old('school_state', $passenger->school_state ?? '') }}" maxlength="2" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('school_state') border-red-500 @enderror" required>
                    @error('school_state')
                        <span class="text-red-600 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('portal.passengers.index', $companyParams) }}" class="px-5 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition">Cancelar</a>
            <button type="submit" class="px-5 py-3 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">{{ $isEditing ? 'Salvar Alterações' : 'Cadastrar Passageiro' }}</button>
        </div>
    </form>
</div>

<script>
const CEP_LOOKUP_URL = '{{ url('/portal/api/cep') }}';

async function lookupCepFor(prefix) {
    const zipInput = document.getElementById(`${prefix}_zip`);
    const feedback = document.getElementById(`${prefix}_zip_feedback`);
    const zip      = (zipInput.value || '').replace(/\D/g, '');

    if (zip.length !== 8) {
        feedback.textContent = 'Informe um CEP válido com 8 dígitos.';
        return;
    }

    feedback.textContent = 'Buscando CEP…';

    try {
        const response = await fetch(`${CEP_LOOKUP_URL}/${zip}`);
        if (!response.ok) throw new Error('not found');

        const data = await response.json();
        const fill = (field, value) => {
            const el = document.getElementById(`${prefix}_${field}`);
            if (el && value) el.value = value;
        };
        fill('street',       data.street);
        fill('neighborhood', data.neighborhood);
        fill('city',         data.city);
        fill('state',        data.state);

        feedback.textContent = 'CEP encontrado. Campos preenchidos automaticamente.';
    } catch {
        feedback.textContent = 'Não foi possível preencher automaticamente este CEP.';
    }
}

function copyResidentialTo(targetPrefix) {
    const fields = ['zip', 'street', 'number', 'complement', 'neighborhood', 'city', 'state'];
    fields.forEach(field => {
        const src = document.getElementById(`residential_${field}`);
        const dst = document.getElementById(`${targetPrefix}_${field}`);
        if (src && dst) dst.value = src.value;
    });
    const feedback = document.getElementById(`${targetPrefix}_zip_feedback`);
    if (feedback) feedback.textContent = 'Copiado do endereço residencial.';
}

function updatePassengerRouteSelectors() {
    const period = document.getElementById('period')?.value;
    const serviceType = document.querySelector('input[name="service_type"]:checked')?.value;
    const directions = {
        ida: serviceType === 'ida' || serviceType === 'ida_volta',
        volta: serviceType === 'volta' || serviceType === 'ida_volta',
    };

    Object.entries(directions).forEach(([direction, enabled]) => {
        const section = document.querySelector(`[data-route-direction-section="${direction}"]`);
        const select = document.querySelector(`[data-route-select="${direction}"]`);
        const emptyMessage = document.querySelector(`[data-route-empty-message="${direction}"]`);

        if (!section || !select) {
            return;
        }

        section.classList.toggle('hidden', !enabled);
        select.disabled = !enabled;

        let visibleOptions = 0;

        Array.from(select.options).forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const matchesPeriod = option.dataset.period === period;
            option.hidden = !matchesPeriod;
            option.disabled = !matchesPeriod;

            if (matchesPeriod) {
                visibleOptions += 1;
            }
        });

        if (!enabled) {
            select.value = '';
        } else if (select.selectedOptions[0] && select.selectedOptions[0].hidden) {
            select.value = '';
        }

        if (emptyMessage) {
            emptyMessage.classList.toggle('hidden', !enabled || visibleOptions > 0);
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    ['residential', 'school', 'pickup', 'dropoff'].forEach(prefix => {
        const zipInput = document.getElementById(`${prefix}_zip`);
        const btn      = document.getElementById(`btn_search_${prefix}_zip`);
        if (!zipInput) return;
        zipInput.addEventListener('blur',  () => lookupCepFor(prefix));
        btn?.addEventListener('click', () => lookupCepFor(prefix));
    });

    document.getElementById('period')?.addEventListener('change', updatePassengerRouteSelectors);
    document.querySelectorAll('input[name="service_type"]').forEach((input) => {
        input.addEventListener('change', updatePassengerRouteSelectors);
    });

    updatePassengerRouteSelectors();
});
</script>
@endsection
