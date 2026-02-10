@extends('layouts/layoutMaster')

@section('title', 'Realizar Checklist de Entrada')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/animate-css/animate.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
      {!! session('error') !!}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible" role="alert">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form action="{{ route('ordens-servico.checklist.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @if($selectedOs)
      <input type="hidden" name="ordem_servico_id" value="{{ $selectedOs->id }}">
      @endif

      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">Dados do {{ niche('entity') }}</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">{{ niche('entity') }}</label>
              @if($selectedOs)
              <input type="hidden" name="veiculo_id" value="{{ $selectedOs->veiculo_id }}">
              <input type="text" class="form-control" value="{{ $selectedOs->veiculo->placa }} - {{ $selectedOs->veiculo->modelo }}" readonly>
              @else
              <select name="veiculo_id" class="form-select select2" required>
                <option value="">Selecione um {{ niche('entity') }}</option>
                @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}">{{ $vehicle->placa }} - {{ $vehicle->modelo }}</option>
                @endforeach
              </select>
              @endif
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">{{ niche('metric') }} Atual</label>
              <input type="number" name="km_current" class="form-control" placeholder="0" required value="{{ $selectedOs->km_entry ?? '' }}">
            </div>
            <div class="col-md-3 mb-3">
              <label class="form-label">Nível de {{ niche('fuel') }}</label>
              <select name="fuel_level" class="form-select" required>
                @foreach(niche('fuel_levels') as $level)
                <option value="{{ $level }}">{{ $level }}</option>
                @endforeach
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
            <input type="text" name="items[INDEX][observations]" class="form-control form-control-sm obs-input mb-1" placeholder="...">

            <!-- Upload de Foto -->
            <div class="d-flex align-items-center mt-1">
              <label class="btn btn-sm btn-icon btn-label-secondary me-2 cursor-pointer" title="Anexar Foto">
                <i class="ti tabler-camera"></i>
                <input type="file" name="items[INDEX][photo]" class="d-none photo-input" accept="image/*">
              </label>
              <span class="photo-preview d-none">
                <img src="" class="rounded border" style="width: 30px; height: 30px; object-fit: cover;">
                <i class="ti tabler-circle-x text-danger cursor-pointer remove-photo ms-1" style="font-size: 14px;"></i>
              </span>
            </div>
          </td>
          <td>
            <button type="button" class="btn btn-sm btn-icon btn-label-danger remove-item"><i class="ti tabler-trash"></i></button>
          </td>
        </tr>
      </template>

      <div class="card mb-4">
        <div class="card-header">
          <h5 class="mb-0">{{ niche('visual_inspection_title') }}</h5>
          <small class="text-muted">{{ niche('visual_inspection_help') }}</small>
        </div>
        <div class="card-body text-center">
          <div id="vehicle-visual-inspection" style="position: relative; display: inline-block; cursor: crosshair;">
            <!-- Car Blueprint Component -->
            @include(niche_config('components.visual_inspection'))

            <!-- Markers Container -->
            <div id="markers-container"></div>
          </div>
          <input type="hidden" name="damage_points_json" id="damage_points_json">
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
    // Inicializa Select2 globalmente
    if (typeof $ !== 'undefined' && $('.select2').length) {
      $('.select2').select2();
    }

    let itemIndex = 0;
    const container = document.getElementById('checklistContainer');
    const emptyState = document.getElementById('emptyState');
    const template = document.getElementById('checklistItemTemplate');

    // --- Visual Inspection Logic ---
    const visualContainer = document.getElementById('vehicle-visual-inspection');
    const markersContainer = document.getElementById('markers-container');
    const damageInput = document.getElementById('damage_points_json');
    let damagePoints = [];

    const carBlueprint = document.getElementById('car-blueprint');

    if (visualContainer && carBlueprint) {
      // Init input
      damageInput.value = JSON.stringify([]);

      carBlueprint.addEventListener('click', function(e) {
        // Ignora cliques nos ícones de remover
        if (e.target.classList.contains('remove-marker')) return;

        const rect = visualContainer.getBoundingClientRect();

        // Corrige offset se o clique for no SVG ou Wrapper
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        // Calculate percentage
        const xPercent = (x / rect.width) * 100;
        const yPercent = (y / rect.height) * 100;

        // Prompt
        Swal.fire({
          title: '{{ niche("visual_inspection_prompt_title") }}',
          input: 'text',
          inputPlaceholder: '{{ niche("visual_inspection_prompt_placeholder") }}',
          showCancelButton: true,
          confirmButtonText: 'Marcar',
          cancelButtonText: 'Cancelar',
          inputValidator: (value) => {
            if (!value) return 'Escreva algo!'
          }
        }).then((result) => {
          if (result.isConfirmed) {
            addDamagePoint(xPercent, yPercent, result.value);
          }
        });
      });
    }

    function addDamagePoint(x, y, note) {
      const id = Date.now();
      const point = {
        id,
        x,
        y,
        note
      };
      damagePoints.push(point);
      updateDamageInput();
      renderMarker(point);
    }

    function removeDamagePoint(id) {
      damagePoints = damagePoints.filter(p => p.id !== id);
      updateDamageInput();
      const el = document.getElementById(`marker-${id}`);
      if (el) el.remove();
    }

    function updateDamageInput() {
      damageInput.value = JSON.stringify(damagePoints);
    }

    function renderMarker(point) {
      const marker = document.createElement('div');
      marker.id = `marker-${point.id}`;
      marker.className = 'damage-marker';
      marker.style.left = point.x + '%';
      marker.style.top = point.y + '%';
      marker.innerHTML = `
            <div class="marker-dot"></div>
            <div class="marker-tooltip">
                <span>${point.note}</span>
                <i class="ti tabler-trash remove-marker" style="cursor:pointer; margin-left:5px;"></i>
            </div>
        `;

      // Remove listener
      marker.querySelector('.remove-marker').addEventListener('click', function(e) {
        e.stopPropagation(); // Stop propagation
        removeDamagePoint(point.id);
      });

      markersContainer.appendChild(marker);
    }
    // --- End Visual Inspection Logic ---

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
      if (container.children.length === 0) {
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
  .btn-check:checked+.btn-outline-success {
    background-color: #28c76f !important;
    color: #fff !important;
  }

  .btn-check:checked+.btn-outline-danger {
    background-color: #ea5455 !important;
    color: #fff !important;
  }

  .btn-check:checked+.btn-outline-secondary {
    background-color: #82868b !important;
    color: #fff !important;
  }

  /* Visual Inspection Markers */
  .damage-marker {
    position: absolute;
    transform: translate(-50%, -50%);
    cursor: pointer;
    z-index: 10;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  @keyframes pulse {
    0% {
      box-shadow: 0 0 0 0 rgba(234, 84, 85, 0.7);
    }

    70% {
      box-shadow: 0 0 0 10px rgba(234, 84, 85, 0);
    }

    100% {
      box-shadow: 0 0 0 0 rgba(234, 84, 85, 0);
    }
  }

  .marker-dot {
    width: 16px;
    height: 16px;
    background-color: rgba(234, 84, 85, 0.9);
    border: 2px solid #fff;
    border-radius: 50%;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
    transition: transform 0.2s;
    animation: pulse 2s infinite;
  }

  .damage-marker:hover .marker-dot {
    transform: scale(1.2);
    animation: none;
  }

  .marker-tooltip {
    display: none;
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: #333;
    color: #fff;
    padding: 5px 10px;
    border-radius: 4px;
    white-space: nowrap;
    font-size: 12px;
    margin-bottom: 5px;
    z-index: 20;
  }

  .damage-marker:hover .marker-tooltip {
    display: flex;
    align-items: center;
    gap: 5px;
  }
</style>
@endsection