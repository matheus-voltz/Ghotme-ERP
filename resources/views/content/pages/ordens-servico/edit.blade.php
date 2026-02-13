@extends('layouts/layoutMaster')

@section('title', 'Editar Ordem de Serviço #' . $order->id)

@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')
@php
$existingServices = $order->items->keyBy('service_id');
$existingParts = $order->parts->keyBy('inventory_item_id');
@endphp

<form action="{{ route('ordens-servico.update', $order->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row">
        <!-- Coluna Esquerda: Dados Gerais -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Informações Básicas</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <select name="client_id" id="client_id" class="select2 form-select" required>
                                <option value="">Selecione o Cliente</option>
                                @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ $order->client_id == $client->id ? 'selected' : '' }}>
                                    {{ $client->name ?? $client->company_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Veículo</label>
                            <select name="veiculo_id" id="veiculo_id" class="select2 form-select" required>
                                <option value="">Selecione o Veículo</option>
                                @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ $order->veiculo_id == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->placa }} - {{ $vehicle->modelo }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Aguardando Início</option>
                                <option value="approved" {{ $order->status == 'approved' ? 'selected' : '' }}>Aprovado</option>
                                <option value="in_progress" {{ $order->status == 'in_progress' ? 'selected' : '' }}>Em Execução</option>
                                <option value="testing" {{ $order->status == 'testing' ? 'selected' : '' }}>Em Teste</option>
                                <option value="cleaning" {{ $order->status == 'cleaning' ? 'selected' : '' }}>Lavagem</option>
                                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Concluído</option>
                                <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Pago</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">KM na Entrada</label>
                            <input type="number" name="km_entry" class="form-control" value="{{ $order->km_entry }}" />
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Descrição Geral do Problema / Relato do Cliente</label>
                        <textarea name="description" class="form-control" rows="3">{{ $order->description }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Serviços -->
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Serviços (Mão de Obra)</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th width="50">Add</th>
                                    <th>Serviço</th>
                                    <th>Preço Un.</th>
                                    <th>Qtd</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($services as $service)
                                @php
                                $hasItem = $existingServices->has($service->id);
                                $item = $hasItem ? $existingServices->get($service->id) : null;
                                @endphp
                                <tr class="{{ $hasItem ? 'table-primary' : '' }}">
                                    <td>
                                        <input type="checkbox" name="services[{{ $service->id }}][selected]" class="form-check-input" {{ $hasItem ? 'checked' : '' }}>
                                    </td>
                                    <td>{{ $service->name }}</td>
                                    <td>
                                        <input type="number" step="0.01" name="services[{{ $service->id }}][price]"
                                            value="{{ $hasItem ? $item->price : $service->price }}"
                                            class="form-control form-control-sm">
                                    </td>
                                    <td>
                                        <input type="number" name="services[{{ $service->id }}][quantity]"
                                            value="{{ $hasItem ? $item->quantity : 1 }}"
                                            class="form-control form-control-sm" style="width: 70px">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Peças -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Peças e Insumos</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th width="50">Add</th>
                                    <th>Peça</th>
                                    <th>Venda Un.</th>
                                    <th>Qtd</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parts as $part)
                                @php
                                $hasPart = $existingParts->has($part->id);
                                $pItem = $hasPart ? $existingParts->get($part->id) : null;
                                @endphp
                                <tr class="{{ $hasPart ? 'table-warning' : '' }}">
                                    <td>
                                        <input type="checkbox" name="parts[{{ $part->id }}][selected]" class="form-check-input" {{ $hasPart ? 'checked' : '' }}>
                                    </td>
                                    <td>{{ $part->name }} <small>(Estoque: {{ $part->quantity }})</small></td>
                                    <td>
                                        <input type="number" step="0.01" name="parts[{{ $part->id }}][price]"
                                            value="{{ $hasPart ? $pItem->price : $part->selling_price }}"
                                            class="form-control form-control-sm">
                                    </td>
                                    <td>
                                        <input type="number" name="parts[{{ $part->id }}][quantity]"
                                            value="{{ $hasPart ? $pItem->quantity : 1 }}"
                                            class="form-control form-control-sm" style="width: 70px">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Direita: Resumo -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Ações</h5>
                </div>
                <div class="card-body pt-4">
                    <button type="submit" class="btn btn-primary w-100 mb-3">Salvar Alterações</button>
                    <a href="{{ route('ordens-servico') }}" class="btn btn-label-secondary w-100">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('.select2').select2();

        // Busca veículos ao mudar o cliente
        $('#client_id').on('change', function() {
            const clientId = $(this).val();
            const vehicleSelect = $('#veiculo_id');

            vehicleSelect.html('<option value="">Carregando...</option>').prop('disabled', true);

            if (clientId) {
                fetch(`{{ url('api/clients') }}/${clientId}/vehicles`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '<option value="">Selecione o Veículo</option>';
                        data.forEach(v => {
                            html += `<option value="${v.id}">${v.placa} - ${v.modelo}</option>`;
                        });
                        vehicleSelect.html(html).prop('disabled', false);
                    });
            }
        });
    });
</script>
@endsection