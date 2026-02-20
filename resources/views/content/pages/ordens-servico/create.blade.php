@extends('layouts/layoutMaster')

@section('title', 'Nova Ordem de Serviço')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('content')
<form action="{{ route('ordens-servico.store') }}" method="POST">
    @csrf
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
                                <option value="{{ $client->id }}">{{ $client->name ?? $client->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ niche('entity') }}</label>
                            <select name="veiculo_id" id="veiculo_id" class="select2 form-select" required disabled>
                                <option value="">Selecione o Cliente Primeiro</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Status Inicial</label>
                            <select name="status" class="form-select">
                                <option value="pending">Aguardando Início</option>
                                <option value="running">Em Execução</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ niche('metric') }} na Entrada</label>
                            <input type="number" name="km_entry" class="form-control" placeholder="0" />
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Descrição Geral do Problema / Relato do Cliente</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
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
                                <tr>
                                    <td><input type="checkbox" name="services[{{ $service->id }}][selected]" class="form-check-input"></td>
                                    <td>{{ $service->name }}</td>
                                    <td><input type="number" step="0.01" name="services[{{ $service->id }}][price]" value="{{ $service->price }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" name="services[{{ $service->id }}][quantity]" value="1" class="form-control form-control-sm" style="width: 70px"></td>
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
                    <h5 class="card-title mb-0">{{ niche('inventory_items') }}</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th width="50">Add</th>
                                    <th>Item</th>
                                    <th>Venda Un.</th>
                                    <th>Qtd</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parts as $part)
                                <tr>
                                    <td><input type="checkbox" name="parts[{{ $part->id }}][selected]" class="form-check-input"></td>
                                    <td>{{ $part->name }} <small>(Estoque: {{ $part->quantity }})</small></td>
                                    <td><input type="number" step="0.01" name="parts[{{ $part->id }}][price]" value="{{ $part->selling_price }}" class="form-control form-control-sm"></td>
                                    <td><input type="number" name="parts[{{ $part->id }}][quantity]" value="1" class="form-control form-control-sm" style="width: 70px"></td>
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
            @if(isset($customFields) && $customFields->count() > 0)
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Informações Adicionais</h5>
                </div>
                <div class="card-body pt-4">
                    @foreach($customFields as $field)
                    <div class="mb-3">
                        <label class="form-label">{{ $field->name }}</label>
                        @if($field->type === 'select')
                            <select name="custom_fields[{{ $field->id }}]" class="form-select" {{ $field->required ? 'required' : '' }}>
                                <option value="">Selecione...</option>
                                @foreach($field->options as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        @elseif($field->type === 'textarea')
                            <textarea name="custom_fields[{{ $field->id }}]" class="form-control" rows="2" {{ $field->required ? 'required' : '' }}></textarea>
                        @else
                            <input type="{{ $field->type }}" name="custom_fields[{{ $field->id }}]" class="form-control" {{ $field->required ? 'required' : '' }} />
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Ações</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="redirect_to_checklist" id="redirect_to_checklist" value="1" checked>
                            <label class="form-check-input-label" for="redirect_to_checklist">Realizar checklist de entrada após salvar</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mb-3">Abrir Ordem de Serviço</button>
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
                fetch(`/api/get-vehicles/${clientId}`)
                    .then(res => {
                        if (!res.ok) throw new Error('Erro na resposta do servidor');
                        return res.json();
                    })
                    .then(data => {
                        let html = '<option value="">Selecione o {{ niche("entity") }}</option>';
                        if (data.length === 0) {
                            html = '<option value="">Nenhum {{ niche("entity") }} encontrado para este cliente</option>';
                        } else {
                            data.forEach(v => {
                                html += `<option value="${v.id}">${v.placa} - ${v.modelo}</option>`;
                            });
                        }
                        vehicleSelect.html(html).prop('disabled', false);
                    })
                    .catch(err => {
                        console.error('Erro ao buscar {{ niche("entities") }}:', err);
                        vehicleSelect.html('<option value="">Erro ao carregar</option>').prop('disabled', true);
                        alert('Não foi possível carregar os {{ niche("entities") }}.');
                    });
            }
        });
    });
</script>
@endsection