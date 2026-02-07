@extends('layouts/layoutMaster')

@section('title', 'Realizar Checklist de Entrada')

@section('content')
<div class="row">
  <div class="col-md-12">
    <form action="{{ route('ordens-servico.checklist.store') }}" method="POST">
      @csrf
      @if($selectedOs)
        <input type="hidden" name="ordem_servico_id" value="{{ $selectedOs->id }}">
      @endif

      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Dados do Veículo</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Veículo</label>
              @if($selectedOs)
                <input type="hidden" name="veiculo_id" value="{{ $selectedOs->veiculo_id }}">
                <input type="text" class="form-control" value="{{ $selectedOs->veiculo->placa }} - {{ $selectedOs->veiculo->modelo }}" readonly>
              @else
                <select name="veiculo_id" class="form-select select2" required>
                  <option value="">Selecione um veículo</option>
                  @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}">{{ $vehicle->placa }} - {{ $vehicle->modelo }}</option>
                  @endforeach
                </select>
              @endif
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">KM Atual</label>
              <input type="number" name="km_current" class="form-control" placeholder="0" required value="{{ $selectedOs->km_entry ?? '' }}">
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Nível de Combustível</label>
              <select name="fuel_level" class="form-select" required>
                <option value="Reserva">Reserva</option>
                <option value="1/4">1/4</option>
                <option value="1/2">1/2</option>
                <option value="3/4">3/4</option>
                <option value="Cheio">Cheio</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Itens de Inspeção</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Status</th>
                  <th>Observação</th>
                </tr>
              </thead>
              <tbody>
                @foreach($checklistItems as $item)
                <tr>
                  <td>{{ $item->name }}</td>
                  <td>
                    <div class="btn-group" role="group">
                      <input type="radio" class="btn-check" name="items[{{ $item->id }}][status]" id="ok-{{ $item->id }}" value="ok" checked>
                      <label class="btn btn-outline-success btn-sm" for="ok-{{ $item->id }}">OK</label>

                      <input type="radio" class="btn-check" name="items[{{ $item->id }}][status]" id="nok-{{ $item->id }}" value="not_ok">
                      <label class="btn btn-outline-danger btn-sm" for="nok-{{ $item->id }}">RUIM</label>

                      <input type="radio" class="btn-check" name="items[{{ $item->id }}][status]" id="na-{{ $item->id }}" value="na">
                      <label class="btn btn-outline-secondary btn-sm" for="na-{{ $item->id }}">N/A</label>
                    </div>
                  </td>
                  <td>
                    <input type="text" name="items[{{ $item->id }}][observations]" class="form-control form-control-sm" placeholder="...">
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Observações Gerais (Riscos, Amassados, etc)</h5>
        </div>
        <div class="card-body">
          <textarea name="notes" class="form-control" rows="3" placeholder="Descreva qualquer detalhe importante..."></textarea>
        </div>
      </div>

      <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary btn-lg">Salvar Checklist</button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Inicializa Select2 se existir
    if(typeof $ !== 'undefined' && $('.select2').length) {
      $('.select2').select2();
    }

    // Listener de Mudança nos Botões do Checklist
    $(document).on('change', '.btn-check', function() {
        const $input = $(this);
        const itemId = $input.attr('id').split('-')[1];
        const status = $input.val();
        const $obsInput = $(`input[name="items[${itemId}][observations]"]`);

        // Feedback visual na linha/input
        if (status === 'not_ok') {
            $obsInput.addClass('border-danger bg-label-danger').attr('placeholder', 'DESCREVA O DEFEITO AQUI...').focus();
        } else {
            $obsInput.removeClass('border-danger bg-label-danger').attr('placeholder', '...');
        }

        // Garante que o rótulo (label) do botão clicado fique em destaque
        // O Bootstrap 5 já faz isso com a classe .btn-check, mas vamos forçar se necessário
        console.log('Item:', itemId, 'Status:', status);
    });
  });
</script>
<style>
    /* Estilo extra para garantir que os botões selecionados fiquem bem visíveis */
    .btn-check:checked + .btn-outline-success { background-color: #28c76f !important; color: #fff !important; }
    .btn-check:checked + .btn-outline-danger { background-color: #ea5455 !important; color: #fff !important; }
    .btn-check:checked + .btn-outline-secondary { background-color: #82868b !important; color: #fff !important; }
</style>
@endsection
