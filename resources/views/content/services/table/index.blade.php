@extends('layouts/layoutMaster')

@section('title', niche('services'))

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('page-script')
@vite(['resources/js/services-table.js'])
@endsection

@section('content')
<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-0">{{ niche('services') }}</h5>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatables-services table border-top"
      data-label-service="{{ niche('service') }}"
      data-label-services="{{ niche('services') }}"
      data-label-price="{{ niche('service_price') }}"
      data-label-time="{{ niche('estimated_time') }}">
      <thead>
        <tr>
          <th>#</th>
          <th>{{ niche('service') }}</th>
          <th>{{ niche('service_price') }} (R$)</th>
          <th>{{ niche('estimated_time') }}</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
    </table>
  </div>

  <!-- Offcanvas Adicionar/Editar -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasService" aria-labelledby="offcanvasServiceLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasServiceLabel" class="offcanvas-title">{{ __('Adicionar') }} {{ niche('service') }}</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
      <form class="pt-0" id="formService">
        @csrf
        <input type="hidden" name="id" id="service_id">
        <div class="mb-6">
          <label class="form-label" for="service-name">{{ __('Nome do') }} {{ niche('service') }}</label>
          <input type="text" class="form-control" id="service-name" name="name" placeholder="{{ niche('service') }}" required />
        </div>
        <div class="mb-6">
          <label class="form-label" for="service-price">{{ niche('service_price') }} (R$)</label>
          <input type="number" step="0.01" class="form-control" id="service-price" name="price" placeholder="0.00" required />
        </div>
        <div class="mb-6">
          <label class="form-label" for="service-time">{{ niche('estimated_time') }} (minutos)</label>
          <input type="number" class="form-control" id="service-time" name="estimated_time" placeholder="60" />
        </div>
        <div class="mb-6">
          <label class="form-label" for="service-description">Descrição</label>
          <textarea class="form-control" id="service-description" name="description" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-primary me-3">Salvar</button>
        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>
      </form>
    </div>
  </div>
</div>

<!-- Modal Ficha de Produção (Ingredientes) -->
<div class="modal fade" id="modalIngredients" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom pb-3">
        <h5 class="modal-title" id="modalIngredientsTitle">Ficha Técnica: <span id="ingredient-service-name" class="fw-bold text-primary"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="ingredient-service-id">

        <!-- Formulário Adicionar Ingrediente -->
        <form id="formAddIngredient" class="row g-3 mb-4 bg-label-secondary p-3 rounded">
          @csrf
          <div class="col-md-5">
            <label class="form-label">Item de Estoque (Insumo)</label>
            <select class="form-select" id="ingredient-item-id" required>
              <option value="">Selecione...</option>
              @foreach($inventoryItems as $item)
              <option value="{{ $item->id }}">{{ $item->name }} (Atual: {{ $item->quantity }} {{ $item->unit_of_measure }})</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Quantidade</label>
            <input type="number" step="0.0001" min="0.0001" class="form-control" id="ingredient-qty" placeholder="Ex: 0.150" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">Medida (UN, KG, L)</label>
            <input type="text" class="form-control" id="ingredient-unit" placeholder="KG" required>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100"><i class="ti tabler-plus"></i> {{ __('Add') }}</button>
          </div>
        </form>

        <!-- Tabela de Ingredientes Atuais -->
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-light">
              <tr>
                <th>Insumo</th>
                <th>Qtd. Gasta</th>
                <th>Custo Unit.</th>
                <th>Subtotal</th>
                <th class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody id="ingredients-table-body">
              <!-- Conteúdo gerado via JS -->
            </tbody>
            <tfoot id="ingredients-table-footer" class="table-light fw-bold" style="display: none;">
              <tr>
                <td colspan="3" class="text-end text-uppercase">Custo Total de Produção:</td>
                <td id="total-recipe-cost" class="text-primary text-nowrap">R$ 0,00</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Combos e Adicionais -->
<div class="modal fade" id="modalAddons" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content bg-body">
      <div class="modal-header border-bottom pb-3">
        <h5 class="modal-title">Combos & Adicionais: <span id="addon-service-name" class="fw-bold text-primary"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
        <input type="hidden" id="addon-service-id">

        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="mb-0">Grupos de Adicionais</h6>
          <button class="btn btn-sm btn-primary" onclick="showAddGroupForm()"><i class="ti tabler-plus"></i> Novo Grupo</button>
        </div>

        <!-- Form Novo Grupo (escondido) -->
        <form id="formAddGroup" class="row g-2 mb-4 bg-label-secondary p-3 rounded" style="display: none;">
          <div class="col-md-5">
            <label class="form-label">Nome (Ex: Escolha o Pão)</label>
            <input type="text" class="form-control" id="group-name" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Tipo Escolha</label>
            <select class="form-select" id="group-type">
              <option value="single">Única (Radio)</option>
              <option value="multiple">Múltipla (Checkbox)</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Mín. Opções</label>
            <input type="number" class="form-control" id="group-min" value="0">
          </div>
          <div class="col-md-2">
            <label class="form-label">Máx. Opções</label>
            <input type="number" class="form-control" id="group-max" placeholder="Ilimitado">
          </div>
          <div class="col-12 mt-2 text-end">
            <button type="button" class="btn btn-sm btn-label-secondary" onclick="hideAddGroupForm()">Cancelar</button>
            <button type="submit" class="btn btn-sm btn-success">Salvar Grupo</button>
          </div>
        </form>

        <!-- Area dos Grupos Listados -->
        <div id="addon-groups-container">
          <!-- Conteúdo dinâmico -->
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

</div>

@push('pricing-script')
<script>
  // Ficha Técnica Lógica
  function openIngredientsModal(serviceId, serviceName) {
    document.getElementById('ingredient-service-id').value = serviceId;
    document.getElementById('ingredient-service-name').innerText = serviceName;

    loadIngredients(serviceId);

    var modal = new bootstrap.Modal(document.getElementById('modalIngredients'));
    modal.show();
  }

  function loadIngredients(serviceId) {
    const tbody = document.getElementById('ingredients-table-body');
    const tfoot = document.getElementById('ingredients-table-footer');
    const totalCostSpan = document.getElementById('total-recipe-cost');

    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Carregando...</td></tr>';
    tfoot.style.display = 'none';

    fetch(`/services/${serviceId}/ingredients`)
      .then(res => res.json())
      .then(data => {
        tbody.innerHTML = '';
        if (data.length === 0) {
          tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Nenhum insumo vinculado. Este produto não dará baixa no estoque automaticamente.</td></tr>';
          return;
        }

        let html = '';
        let totalRecipeCost = 0;

        data.forEach(ing => {
          const cost = parseFloat(ing.cost_price || 0);
          const subtotal = parseFloat(ing.subtotal || 0);
          totalRecipeCost += subtotal;

          html += `
            <tr>
              <td>${ing.inventory_item_name}</td>
              <td class="text-nowrap">${ing.quantity} ${ing.unit_of_measure}</td>
              <td class="text-nowrap">R$ ${cost.toFixed(2)}</td>
              <td class="text-nowrap fw-medium text-heading">R$ ${subtotal.toFixed(2)}</td>
              <td class="text-center">
                <button class="btn btn-sm btn-icon btn-outline-danger" onclick="removeIngredient(${serviceId}, ${ing.id})">
                  <i class="ti tabler-trash"></i>
                </button>
              </td>
            </tr>
          `;
        });

        tbody.innerHTML = html;
        totalCostSpan.innerText = `R$ ${totalRecipeCost.toFixed(2)}`;
        tfoot.style.display = 'table-footer-group';
      });
  }

  document.getElementById('formAddIngredient')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const serviceId = document.getElementById('ingredient-service-id').value;
    const itemId = document.getElementById('ingredient-item-id').value;
    const qty = document.getElementById('ingredient-qty').value;
    const unit = document.getElementById('ingredient-unit').value;

    fetch(`/services/${serviceId}/ingredients`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          inventory_item_id: itemId,
          quantity: qty,
          unit_of_measure: unit
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          document.getElementById('ingredient-item-id').value = '';
          document.getElementById('ingredient-qty').value = '';
          loadIngredients(serviceId);
          // Pode usar sweetalert (Swal) aqui tbm
        } else {
          alert("Erro ao adicionar ingrediente.");
        }
      });
  });

  function removeIngredient(serviceId, ingredientId) {
    if (!confirm("Remover este insumo da ficha técnica?")) return;

    fetch(`/services/${serviceId}/ingredients/${ingredientId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          loadIngredients(serviceId);
        }
      });
  }

  // ---- Combos e Adicionais Lógica ----
  function openAddonsModal(serviceId, serviceName) {
    document.getElementById('addon-service-id').value = serviceId;
    document.getElementById('addon-service-name').innerText = serviceName;
    hideAddGroupForm();
    loadAddonGroups(serviceId);

    var modal = new bootstrap.Modal(document.getElementById('modalAddons'));
    modal.show();
  }

  function showAddGroupForm() {
    document.getElementById('formAddGroup').style.display = 'flex';
  }

  function hideAddGroupForm() {
    document.getElementById('formAddGroup').style.display = 'none';
    document.getElementById('formAddGroup').reset();
  }

  document.getElementById('formAddGroup')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const serviceId = document.getElementById('addon-service-id').value;
    const body = {
      name: document.getElementById('group-name').value,
      selection_type: document.getElementById('group-type').value,
      min_options: document.getElementById('group-min').value,
      max_options: document.getElementById('group-max').value || null
    };

    fetch(`/services/${serviceId}/addons/groups`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify(body)
    }).then(r => r.json()).then(d => {
      if (d.success) {
        hideAddGroupForm();
        loadAddonGroups(serviceId);
      } else {
        alert("Erro: " + d.message);
      }
    });
  });

  function deleteAddonGroup(groupId) {
    if (!confirm('Excluir este grupo de adicionais inteiro?')) return;
    const serviceId = document.getElementById('addon-service-id').value;
    fetch(`/services/addons/groups/${groupId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    }).then(r => r.json()).then(d => {
      if (d.success) loadAddonGroups(serviceId);
    });
  }

  function addServiceAddon(groupId) {
    const nameInput = document.getElementById(`addon-name-${groupId}`);
    const priceInput = document.getElementById(`addon-price-${groupId}`);
    if (!nameInput.value || !priceInput.value) return;

    fetch(`/services/addons/groups/${groupId}/items`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        name: nameInput.value,
        price: priceInput.value
      })
    }).then(r => r.json()).then(d => {
      if (d.success) {
        loadAddonGroups(document.getElementById('addon-service-id').value);
      }
    });
  }

  function deleteServiceAddon(addonId) {
    if (!confirm('Excluir este adicional?')) return;
    fetch(`/services/addons/items/${addonId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    }).then(r => r.json()).then(d => {
      if (d.success) loadAddonGroups(document.getElementById('addon-service-id').value);
    });
  }

  function loadAddonGroups(serviceId) {
    const container = document.getElementById('addon-groups-container');
    container.innerHTML = '<div class="text-center py-4"><div class="spinner-border"></div></div>';

    fetch(`/services/${serviceId}/addons`)
      .then(r => r.json())
      .then(groups => {
        if (groups.length === 0) {
          container.innerHTML = '<p class="text-muted text-center py-4">Nenhum adicional configurado.</p>';
          return;
        }

        let html = '';
        groups.forEach(g => {
          let selectionText = g.selection_type == 'single' ? 'Uma opção (Radio)' : 'Várias opções (Checkbox)';
          let itemsHtml = '';
          g.addons.forEach(item => {
            itemsHtml += `
              <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-1">
                <span>${item.name}</span>
                <div class="d-flex align-items-center gap-3">
                  <span class="badge bg-label-success">+ R$ ${parseFloat(item.price).toFixed(2)}</span>
                  <button class="btn btn-xs btn-text-danger p-0" onclick="deleteServiceAddon(${item.id})"><i class="ti tabler-trash"></i></button>
                </div>
              </li>
             `;
          });

          html += `
            <div class="card shadow-none border mb-3">
              <div class="card-header border-bottom py-2 px-3 d-flex justify-content-between bg-lighter">
                <div>
                  <h6 class="mb-0">${g.name}</h6>
                  <small class="text-muted">${selectionText} | Min: ${g.min_options} / Max: ${g.max_options || 'Livre'}</small>
                </div>
                <button class="btn btn-sm btn-icon btn-text-danger" onclick="deleteAddonGroup(${g.id})"><i class="ti tabler-trash"></i></button>
              </div>
              <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                  ${itemsHtml}
                  <li class="list-group-item bg-label-secondary px-3 py-2">
                    <div class="row g-2 align-items-center">
                      <div class="col-6"><input type="text" class="form-control form-control-sm" id="addon-name-${g.id}" placeholder="Nome do Adicional (Ex: Bacon)"></div>
                      <div class="col-4"><input type="number" step="0.01" min="0" class="form-control form-control-sm" id="addon-price-${g.id}" placeholder="Valor (Ex: 2.00)"></div>
                      <div class="col-2 text-end"><button class="btn btn-sm btn-primary w-100" onclick="addServiceAddon(${g.id})">Add</button></div>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          `;
        });
        container.innerHTML = html;
      });
  }

  // Exportar para o window caso chamado do datatable
  window.openIngredientsModal = openIngredientsModal;
  window.removeIngredient = removeIngredient;
  window.openAddonsModal = openAddonsModal;
  window.showAddGroupForm = showAddGroupForm;
  window.hideAddGroupForm = hideAddGroupForm;
  window.deleteAddonGroup = deleteAddonGroup;
  window.addServiceAddon = addServiceAddon;
  window.deleteServiceAddon = deleteServiceAddon;
</script>
@endpush
@endsection