@extends('layouts/layoutMaster')

@section('title', 'Editar ' . niche('entity') . ' #' . $order->id)

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
                                @if($client)
                                <option value="{{ $client->id }}" selected>
                                    {{ $client->name ?? $client->company_name }}
                                </option>
                                @else
                                <option value="" selected>Consumidor Final</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ get_current_niche() === 'food_service' ? 'Mesa / Senha' : niche('entity') }}</label>
                            <select name="veiculo_id" id="veiculo_id" class="select2 form-select" {{ get_current_niche() === 'food_service' ? '' : 'required' }}>
                                <option value="">{{ get_current_niche() === 'food_service' ? 'Sem Mesa (Balcão)' : 'Selecione o ' . niche('entity') }}</option>
                                @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ ($order->veiculo_id ?? 0) == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->placa }} {{ $vehicle->modelo }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Status do Pedido</label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Na Fila (Pendente)</option>
                                <option value="in_progress" {{ $order->status == 'in_progress' ? 'selected' : '' }}>Em Preparo</option>
                                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Pronto para Entrega</option>
                                <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>Finalizado / Pago</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">{{ get_current_niche() === 'food_service' ? 'Observações do Pedido (Ex: Sem cebola, Mal passado)' : 'Descrição Geral do Problema' }}</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Digite aqui observações importantes para a cozinha...">{{ $order->description }}</textarea>
                    </div>
                </div>
            </div>

            @if(get_current_niche() !== 'food_service')
            <!-- Serviços (Apenas para outros nichos) -->
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
            @endif

            <!-- Itens do Pedido (Lanches, Bebidas...) -->
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">{{ get_current_niche() === 'food_service' ? 'Itens do Pedido' : niche('inventory_items') }}</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th width="50">Add</th>
                                    <th>{{ get_current_niche() === 'food_service' ? 'Produto' : 'Item' }}</th>
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
                                <tr class="{{ $hasPart ? (get_current_niche() === 'food_service' ? 'table-primary' : 'table-warning') : '' }}">
                                    <td>
                                        <input type="checkbox" name="parts[{{ $part->id }}][selected]" class="form-check-input" {{ $hasPart ? 'checked' : '' }}>
                                    </td>
                                    <td>{{ $part->name }} @if(get_current_niche() !== 'food_service') <small>(Estoque: {{ $part->quantity }})</small> @endif</td>
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
            <!-- Card de Status do Preparo -->
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Status do Preparo</h5>
                </div>
                <div class="card-body pt-4 text-center">
                    @php
                    $statusColor = match($order->status) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        'paid' => 'primary',
                        default => 'secondary'
                    };
                    $statusLabel = match($order->status) {
                        'pending' => 'Na Fila',
                        'in_progress' => 'Preparando',
                        'completed' => 'Pronto p/ Entrega',
                        'paid' => 'Entregue / Pago',
                        default => $order->status
                    };
                    @endphp
                    <div class="mb-3">
                        <span class="badge bg-label-{{ $statusColor }} fs-5 px-4 py-2">{{ $statusLabel }}</span>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3">Salvar Alterações</button>
                    <a href="{{ route('ordens-servico') }}" class="btn btn-label-secondary w-100">Voltar ao Monitor</a>
                </div>
            </div>

            <!-- Card de Faturamento / NF-e (Oculto para Food Service por enquanto, a menos que necessário) -->
            @if(get_current_niche() !== 'food_service')
            <div class="card mb-4 border-primary">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Faturamento / NF-e</h5>
                    <i class="ti tabler-receipt-tax text-primary"></i>
                </div>
                <div class="card-body pt-4">
                    @php
                    $invoice = \App\Models\TaxInvoice::where('ordem_servico_id', $order->id)->first();
                    @endphp

                    @if($invoice)
                    <div class="alert alert-success d-flex align-items-center mb-3" role="alert">
                        <span class="alert-icon rounded-circle p-1 me-2"><i class="ti tabler-check"></i></span>
                        <span>Nota Fiscal #{{ $invoice->invoice_number }} emitida</span>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ $invoice->pdf_url }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="ti tabler-file-type-pdf me-1"></i> Baixar PDF
                        </a>
                        <a href="{{ $invoice->xml_url }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <i class="ti tabler-file-code me-1"></i> Baixar XML
                        </a>
                    </div>
                    @else
                    <p class="small text-muted mb-3">Esta {{ niche('entity') }} ainda não possui nota fiscal emitida.</p>
                    <a href="{{ route('tax.invoice.create', ['os' => $order->id]) }}" class="btn btn-primary w-100">
                        <i class="ti tabler-send me-1"></i> Emitir Nota Fiscal
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Card de Vistoria Visual (Oculto para Food Service) -->
            @if(get_current_niche() !== 'food_service' && $order->inspection && $order->inspection->damagePoints->isNotEmpty())
            <div class="card">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Fotos da Vistoria</h5>
                    <span class="badge bg-label-primary">{{ $order->inspection->damagePoints->count() }}</span>
                </div>
                <div class="card-body pt-4">
                    <div class="row g-3">
                        @foreach($order->inspection->damagePoints as $point)
                        <div class="col-6">
                            <div class="card shadow-none border border-light h-100">
                                @if($point->photo_path)
                                <a href="{{ asset('storage/' . $point->photo_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $point->photo_path) }}" class="card-img-top rounded" alt="{{ $point->part_name }}" style="height: 100px; object-fit: cover;">
                                </a>
                                @else
                                <div class="bg-label-secondary d-flex align-items-center justify-content-center rounded-top" style="height: 100px">
                                    <i class="ti tabler-camera-off fs-2"></i>
                                </div>
                                @endif
                                <div class="card-body p-2 text-center">
                                    <small class="fw-bold d-block text-truncate">{{ $point->part_name }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('ordens-servico.checklist.show', $order->inspection->id) }}" class="btn btn-outline-primary btn-sm w-100">Ver Detalhes da Vistoria</a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#client_id').select2({
            placeholder: "Buscar cliente pelo nome, CPF, CNPJ ou email...",
            minimumInputLength: 1,
            ajax: {
                url: '/api/clients/search',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            }
        });

        // Busca veículos ao mudar o cliente
        $('#client_id').on('change', function() {
            const clientId = $(this).val();
            const vehicleSelect = $('#veiculo_id');

            vehicleSelect.html('<option value="">Carregando...</option>').prop('disabled', true);

            if (clientId) {
                fetch(`{{ url('api/get-vehicles') }}/${clientId}`)
                    .then(res => {
                        if (!res.ok) throw new Error('Erro ao buscar {{ mb_strtolower(niche("entities"), "UTF-8") }}');
                        return res.json();
                    })
                    .then(data => {
                        let html = '<option value="">Selecione o {{ niche("entity") }}</option>';
                        if (data.length === 0) {
                            html = '<option value="">Nenhum {{ mb_strtolower(niche("entity"), "UTF-8") }} encontrado para este cliente</option>';
                        } else {
                            data.forEach(v => {
                                let badge = v.placa ? v.placa + ' - ' : '';
                                html += `<option value="${v.id}">${badge}${v.modelo}</option>`;
                            });
                        }
                        vehicleSelect.html(html).prop('disabled', false);
                    })
                    .catch(err => {
                        console.error('Erro:', err);
                        vehicleSelect.html('<option value="">Erro ao carregar</option>').prop('disabled', false);
                        alert('Não foi possível carregar os {{ mb_strtolower(niche("entities"), "UTF-8") }} deste cliente. Tente novamente.');
                    });
            }
        });
    });
</script>
@endsection