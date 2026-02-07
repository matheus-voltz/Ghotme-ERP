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
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Itens de Inspeção</h5>
          <button type="button" class="btn btn-primary btn-sm" id="btnAddChecklistItem">
            <i class="ti tabler-plus me-1"></i> Adicionar Item
          </button>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered" id="checklistTable">
              <thead>
                <tr>
                  <th style="width: 40%;">Item</th>
                  <th style="width: 30%;">Status</th>
                  <th>Observação</th>
                  <th style="width: 50px;"></th>
                </tr>
              </thead>
              <tbody id="checklistContainer">
                <!-- Itens serão adicionados aqui via JS -->
              </tbody>
            </table>
          </div>
          <div id="emptyState" class="text-center py-5 text-muted">
            <i class="ti tabler-list-check display-6 mb-2"></i>
            <p>Nenhum item adicionado ainda.</p>
          </div>
        </div>
      </div>

      <!-- Template Oculto para Novos Itens -->
      <template id="checklistItemTemplate">
        <tr>
            <td>
                <select name="items[INDEX][id]" class="form-select form-select-sm item-select" required>
                    <option value="">Selecione...</option>
                    @foreach($checklistItems as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check status-ok" name="items[INDEX][status]" id="ok-INDEX" value="ok" checked>
                    <label class="btn btn-outline-success btn-sm" for="ok-INDEX">OK</label>

                    <input type="radio" class="btn-check status-nok" name="items[INDEX][status]" id="nok-INDEX" value="not_ok">
                    <label class="btn btn-outline-danger btn-sm" for="nok-INDEX">RUIM</label>

                    <input type="radio" class="btn-check status-na" name="items[INDEX][status]" id="na-INDEX" value="na">
                    <label class="btn btn-outline-secondary btn-sm" for="na-INDEX">N/A</label>
                </div>
            </td>
            <td>
                <input type="text" name="items[INDEX][observations]" class="form-control form-control-sm obs-input" placeholder="...">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item"><i class="ti tabler-trash"></i></button>
            </td>
        </tr>
      </template>

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
    // Inicializa Select2 globalmente
    if(typeof $ !== 'undefined' && $('.select2').length) {
      $('.select2').select2();
    }

    let itemIndex = 0;
    const container = document.getElementById('checklistContainer');
    const emptyState = document.getElementById('emptyState');
    const template = document.getElementById('checklistItemTemplate');

    // Adicionar Item
    document.getElementById('btnAddChecklistItem').addEventListener('click', function() {
        itemIndex++;
        const clone = template.content.cloneNode(true);
        
        // Substitui INDEX pelo número atual para garantir IDs únicos
        clone.querySelectorAll('[name*="INDEX"]').forEach(el => {
            el.name = el.name.replace('INDEX', itemIndex);
        });
        clone.querySelectorAll('[id*="INDEX"]').forEach(el => {
            el.id = el.id.replace('INDEX', itemIndex);
        });
        clone.querySelectorAll('label[for*="INDEX"]').forEach(el => {
            el.setAttribute('for', el.getAttribute('for').replace('INDEX', itemIndex));
        });

        container.appendChild(clone);
        emptyState.classList.add('d-none');
    });

    // Remover Item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('tr').remove();
        if(container.children.length === 0) {
            emptyState.classList.remove('d-none');
        }
    });

    // Interatividade dos Botões (OK/RUIM)
    $(document).on('change', '.btn-check', function() {
        const $row = $(this).closest('tr');
        const status = $(this).val();
        const $obsInput = $row.find('.obs-input');

        if (status === 'not_ok') {
            $obsInput.addClass('border-danger bg-label-danger').attr('placeholder', 'DESCREVA O DEFEITO...').focus();
        } else {
            $obsInput.removeClass('border-danger bg-label-danger').attr('placeholder', '...');
        }
    });
  });
</script>
<style>
    /* Estilo para botões de rádio selecionados */
    .btn-check:checked + .btn-outline-success { background-color: #28c76f !important; color: #fff !important; }
    .btn-check:checked + .btn-outline-danger { background-color: #ea5455 !important; color: #fff !important; }
    .btn-check:checked + .btn-outline-secondary { background-color: #82868b !important; color: #fff !important; }
</style>
@endsection
