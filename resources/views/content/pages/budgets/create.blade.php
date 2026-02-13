@extends('layouts/layoutMaster')

@section('title', 'Novo Orçamento')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/flatpickr/flatpickr.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js'])
@endsection

@section('content')
<form action="{{ route('budgets.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Dados do Orçamento</h5>
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
                            <label class="form-label">Veículo</label>
                            <select name="veiculo_id" id="veiculo_id" class="select2 form-select" required disabled>
                                <option value="">Selecione o Cliente Primeiro</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Válido até</label>
                            <input type="text" name="valid_until" class="form-control flatpickr" value="{{ now()->addDays($validityDays)->format('Y-m-d') }}" required />
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Descrição / Observações</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>

            <!-- Serviços -->
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Mão de Obra</h5>
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
                    <h5 class="card-title mb-0">Peças Sugeridas</h5>
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
                                <tr>
                                    <td><input type="checkbox" name="parts[{{ $part->id }}][selected]" class="form-check-input"></td>
                                    <td>{{ $part->name }}</td>
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

        <div class="col-md-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Finalizar</h5>
                </div>
                <div class="card-body pt-4">
                    <button type="submit" class="btn btn-primary w-100 mb-3">Gerar Orçamento</button>
                    <a href="{{ route('budgets.pending') }}" class="btn btn-label-secondary w-100">Cancelar</a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('.select2').select2();
        $('.flatpickr').flatpickr();

        $('#client_id').on('change', function() {
            const clientId = $(this).val();
            const vehicleSelect = $('#veiculo_id');
            vehicleSelect.html('<option value="">Carregando...</option>').prop('disabled', true);

            if (clientId) {
                fetch(`{{ url('api/clients') }}/${clientId}/vehicles`)
                    .then(res => {
                        if (!res.ok) throw new Error('Erro ao buscar veículos');
                        return res.json();
                    })
                    .then(data => {
                        let html = '<option value="">Selecione o Veículo</option>';
                        if (data.length === 0) {
                            html = '<option value="">Nenhum veículo encontrado para este cliente</option>';
                        } else {
                            data.forEach(v => {
                                html += `<option value="${v.id}">${v.placa} - ${v.modelo}</option>`;
                            });
                        }
                        vehicleSelect.html(html).prop('disabled', false);
                    })
                    .catch(err => {
                        console.error('Erro:', err);
                        vehicleSelect.html('<option value="">Erro ao carregar</option>').prop('disabled', false);
                        alert('Não foi possível carregar os veículos deste cliente. Tente novamente.');
                    });
            }
        });
    });
</script>
@endsection