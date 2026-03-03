@extends('layouts/layoutMaster')

@section('title', 'Frente de Caixa')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="py-3 mb-4">
    <span class="text-muted fw-light">Financeiro /</span> Frente de Caixa
  </h4>

  @if(session('success'))
  <div class="alert alert-success alert-dismissible" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  @if(session('error'))
  <div class="alert alert-danger alert-dismissible" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  @endif

  <div class="row mb-4">
    <!-- Dashboard do Caixa -->
    <div class="col-12 mb-4">
      <div class="card {{ $currentRegister ? 'border-success' : 'border-warning' }}">
        <div class="card-body d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-1 d-flex align-items-center gap-2">
              <i class="ti {{ $currentRegister ? 'tabler-lock-open text-success' : 'tabler-lock text-warning' }} fs-2"></i>
              Caixa {{ $currentRegister ? 'Aberto' : 'Fechado' }}
            </h4>
            @if($currentRegister)
            <p class="mb-0 text-muted">Aberto hoje às {{ $currentRegister->opened_at->format('H:i') }} | Fundo Inicial: R$ {{ number_format($currentRegister->opening_balance, 2, ',', '.') }}</p>
            @else
            <p class="mb-0 text-muted">Para iniciar as vendas do dia, abra o caixa informando o troco inicial.</p>
            @endif
          </div>

          <div class="d-flex gap-2">
            @if($currentRegister)
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#movementModal"><i class="ti tabler-arrows-right-left me-1"></i> Movimentar</button>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#closeRegisterModal"><i class="ti tabler-lock me-1"></i> Fechar Caixa</button>
            @else
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#openRegisterModal"><i class="ti tabler-lock-open me-1"></i> Abrir Caixa</button>
            @endif
          </div>
        </div>
      </div>
    </div>

    @if($currentRegister)
    <!-- PONTO DE VENDA (PDV) -->
    <div class="col-12">
      <div class="row">
        <!-- GRADE DE PRODUTOS -->
        <div class="col-md-8">
          <div class="card h-100">
            <div class="card-header border-bottom d-flex justify-content-between">
              <h5 class="mb-0">Produtos</h5>
              <div class="input-group input-group-sm" style="width: 250px;">
                <span class="input-group-text"><i class="ti tabler-search"></i></span>
                <input type="text" class="form-control" placeholder="Buscar produto..." id="searchProduct">
              </div>
            </div>
            <div class="card-body pt-4">
              <div class="row g-3" id="products-grid">
                @forelse($services as $service)
                <div class="col-sm-6 col-md-4 col-lg-3 product-item" data-name="{{ strtolower($service->name) }}">
                  <div class="card border cursor-pointer h-100 product-card"
                    data-id="{{ $service->id }}"
                    data-title="{{ $service->name }}"
                    data-price="{{ $service->price }}"
                    onclick="addToCart(this)">
                    @if($service->mainImage)
                    <img src="{{ asset('storage/' . $service->mainImage->path) }}" class="card-img-top object-fit-cover" style="height: 120px;" alt="{{ $service->name }}">
                    @else
                    <div class="card-img-top bg-label-secondary d-flex align-items-center justify-content-center" style="height: 120px;">
                      <i class="ti tabler-photo fs-2 text-muted"></i>
                    </div>
                    @endif
                    <div class="card-body p-2 text-center d-flex flex-column justify-content-between">
                      <h6 class="mb-1 text-truncate" title="{{ $service->name }}">{{ $service->name }}</h6>
                      <span class="badge bg-label-success fs-6">R$ {{ number_format($service->price, 2, ',', '.') }}</span>
                    </div>
                  </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                  <p class="text-muted">Nenhum serviço/produto cadastrado.</p>
                </div>
                @endforelse
              </div>
            </div>
          </div>
        </div>

        <!-- CARRINHO / COMANDA -->
        <div class="col-md-4">
          <div class="card h-100 d-flex flex-column">
            <div class="card-header border-bottom bg-label-primary">
              <h5 class="mb-0 text-primary d-flex align-items-center gap-2">
                <i class="ti tabler-shopping-cart"></i> Comanda Atual
              </h5>
            </div>
            <div class="card-body p-0 flex-grow-1" style="max-height: 400px; overflow-y: auto;">
              <!-- Lista Vazia -->
              <div id="empty-cart-msg" class="text-center py-5 text-muted">
                <i class="ti tabler-shopping-cart-x fs-1 mb-2"></i>
                <p>Clique nos produtos ao lado<br>para adicionar à comanda.</p>
              </div>

              <!-- Itens do Carrinho -->
              <ul class="list-group list-group-flush" id="cart-items" style="display: none;">
                <!-- Items will be injected via JS -->
              </ul>
            </div>
            <div class="card-footer border-top bg-lighter mt-auto p-3">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fs-5 text-heading font-weight-bold">Total:</span>
                <span class="fs-3 fw-bold text-success" id="cart-total">R$ 0,00</span>
              </div>
              <button type="button" class="btn btn-success btn-lg w-100 fw-bold" id="btn-checkout" disabled onclick="openCheckout()">
                COBRAR
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>

  <input type="hidden" id="current-register-id" value="{{ $currentRegister->id ?? '' }}">

  <!-- Histórico de Caixas -->
  <div class="card mt-4">
    <div class="card-header">
      <h5 class="mb-0">Histórico de Fechamentos</h5>
    </div>
    <div class="table-responsive text-nowrap">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Data Abertura</th>
            <th>Data Fechamento</th>
            <th>Operador</th>
            <th>Saldo Final Apurado</th>
            <th>Diferença</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          @forelse($history as $reg)
          <tr>
            <td>{{ $reg->opened_at->format('d/m/Y H:i') }}</td>
            <td>{{ $reg->closed_at->format('d/m/Y H:i') }}</td>
            <td>{{ $reg->user->name }}</td>
            <td>R$ {{ number_format($reg->actual_balance, 2, ',', '.') }}</td>
            <td>
              @if($reg->difference > 0)
              <span class="text-success">+ R$ {{ number_format($reg->difference, 2, ',', '.') }}</span>
              @elseif($reg->difference < 0)
                <span class="text-danger">- R$ {{ number_format(abs($reg->difference), 2, ',', '.') }}</span>
                @else
                <span class="text-muted">Exato</span>
                @endif
            </td>
            <td>
              <a href="{{ route('cash-register.show', $reg->id) }}" class="btn btn-sm btn-icon btn-outline-primary">
                <i class="ti tabler-eye"></i>
              </a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="text-center">Nenhum histórico encontrado.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Abrir Caixa -->
<div class="modal fade" id="openRegisterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Abrir Caixa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('cash-register.open') }}" method="POST">
        @csrf
        <div class="modal-body">
          <p>Informe o valor do fundo de troco inicial para o turno.</p>
          <div class="row">
            <div class="col mb-3">
              <label for="opening_balance" class="form-label">Saldo Inicial (Troco) - R$</label>
              <input type="number" step="0.01" min="0" id="opening_balance" name="opening_balance" class="form-control" value="0" required />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Abrir Caixa</button>
        </div>
      </form>
    </div>
  </div>
</div>

@if($currentRegister)
<!-- Modal Fechar Caixa -->
<div class="modal fade" id="closeRegisterModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content border-danger">
      <div class="modal-header border-bottom">
        <h5 class="modal-title text-danger">Fechar Caixa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('cash-register.close', $currentRegister->id) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="alert alert-warning mb-4">
            O sistema espera encontrar <strong>R$ {{ number_format($currentRegister->calculated_balance, 2, ',', '.') }}</strong> na gaveta.
          </div>

          <div class="row">
            <div class="col mb-3">
              <label for="actual_balance" class="form-label">Valor Apurado na Gaveta - R$</label>
              <input type="number" step="0.01" min="0" id="actual_balance" name="actual_balance" class="form-control form-control-lg" required />
              <small class="text-muted">Conte o dinheiro e insira o valor real.</small>
            </div>
          </div>
          <div class="row">
            <div class="col mb-3">
              <label for="notes" class="form-label">Observações (Opcional)</label>
              <textarea id="notes" name="notes" class="form-control" rows="2" placeholder="Justifique possíveis sobras ou quebras de caixa..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Confirmar Fechamento</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Checkout (PDV Rápido) -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header border-bottom pb-4 mb-4">
        <h5 class="modal-title d-flex align-items-center gap-2">
          <i class="ti tabler-cash"></i> Finalizar Venda
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-label-success rounded">
          <span class="fs-5">Total a Pagar:</span>
          <span class="fs-2 fw-bold text-success" id="checkout-total">R$ 0,00</span>
        </div>

        <div class="row">
          <div class="col-12 mb-3">
            <label class="form-label">Forma de Pagamento</label>
            <div class="input-group">
              <select id="checkout-payment-method" class="form-select form-select-lg">
                <option value="dinheiro">Dinheiro</option>
                <option value="pix">PIX</option>
                <option value="debito">Cartão de Débito</option>
                <option value="credito">Cartão de Crédito</option>
                <option value="fiado">Fiado (Anotar no Cliente)</option>
              </select>
            </div>
          </div>

          <div class="col-12 mb-3" id="client-select-wrapper" style="display: none;">
            <label class="form-label">Cliente (Obrigatório para Fiado)</label>
            <select id="checkout-client" class="form-select">
              <!-- Clientes poderiam ser carregados aqui, futuramente -->
              <option value="">Selecione um cliente (A implementar)</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer pt-4 border-top">
        <button type="button" class="btn btn-label-secondary w-100 mb-2" data-bs-dismiss="modal">Voltar</button>
        <button type="button" class="btn btn-success btn-lg w-100" id="btn-confirm-checkout" onclick="processCheckout()">
          <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
          Confirmar Pagamento e Finalizar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Selecionar Adicionais -->
<div class="modal fade" id="selectAddonsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content bg-body">
      <div class="modal-header border-bottom pb-3">
        <h5 class="modal-title">Escolha os Complementos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
        <div id="select-addons-body">
          <!-- Injetado por JS -->
        </div>
      </div>
      <div class="modal-footer pt-3 border-top">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="confirmAddProductWithAddons()">
          Adicionar à Comanda
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Movimentação (Sangria/Suprimento) -->
<div class="modal fade" id="movementModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Movimentar Caixa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('cash-register.movement', $currentRegister->id) }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col">
              <label class="form-label">Tipo de Movimentação</label>
              <select name="type" class="form-select" required>
                <option value="">Selecione...</option>
                <option value="sangria">Sangria (Retirada de dinheiro da gaveta)</option>
                <option value="suprimento">Suprimento (Adição de troco extra)</option>
              </select>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col">
              <label for="amount" class="form-label">Valor - R$</label>
              <input type="number" step="0.01" min="0.01" id="amount" name="amount" class="form-control" required />
            </div>
          </div>
          <div class="row">
            <div class="col">
              <label for="description" class="form-label">Descrição</label>
              <input type="text" id="description" name="description" class="form-control" placeholder="Ex: Pagamento fornecedor padaria" />
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Registrar</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

@endsection

@section('page-script')
<script>
  let cart = {};
  let tempProductForAddons = null;

  function addToCart(element) {
    let id = element.getAttribute('data-id');
    let name = element.getAttribute('data-title');
    let price = parseFloat(element.getAttribute('data-price'));

    // Check if service has addons
    fetch(`/services/${id}/addons`)
      .then(r => r.json())
      .then(groups => {
        if (groups.length === 0) {
          // No addons, add directly
          insertIntoCart(id, name, price, []);
        } else {
          // Open Modal to select
          tempProductForAddons = {
            id: id,
            name: name,
            basePrice: price,
            groups: groups
          };
          renderAddonSelectionModal(groups);
          var myModal = new bootstrap.Modal(document.getElementById('selectAddonsModal'));
          myModal.show();
        }
      });
  }

  function renderAddonSelectionModal(groups) {
    let html = '';
    groups.forEach(g => {
      if (g.addons.length === 0) return;
      let req = g.min_options > 0 ? `<span class="text-danger">*</span>` : '';
      let inputType = g.selection_type === 'single' ? 'radio' : 'checkbox';

      html += `
         <div class="mb-4 addon-group" data-min="${g.min_options}" data-max="${g.max_options || 999}" data-group="group-${g.id}">
           <label class="form-label fw-bold d-block border-bottom pb-1">${g.name} ${req}</label>
       `;

      g.addons.forEach(item => {
        let priceTag = parseFloat(item.price) > 0 ? ` (+R$ ${parseFloat(item.price).toFixed(2)})` : '';
        html += `
            <div class="form-check">
              <input class="form-check-input addon-option" type="${inputType}" name="group-${g.id}" value="${item.id}" data-name="${item.name}" data-price="${item.price}">
              <label class="form-check-label">${item.name} <span class="text-muted"><small>${priceTag}</small></span></label>
            </div>
          `;
      });
      html += '</div>';
    });
    document.getElementById('select-addons-body').innerHTML = html;
  }

  function confirmAddProductWithAddons() {
    let selectedAddons = [];
    let isValid = true;

    // Validate if the minimum requirements are met
    document.querySelectorAll('.addon-group').forEach(groupDiv => {
      let min = parseInt(groupDiv.getAttribute('data-min'));
      let max = parseInt(groupDiv.getAttribute('data-max'));
      let groupName = groupDiv.getAttribute('data-group');

      let checked = document.querySelectorAll(`input[name="${groupName}"]:checked`);
      if (checked.length < min) {
        alert(`Você precisa selecionar pelo menos ${min} opção(ões) no grupo.`);
        isValid = false;
        return;
      }
      if (checked.length > max) {
        alert(`Selecione no máximo ${max} opções.`);
        isValid = false;
        return;
      }

      checked.forEach(chk => {
        selectedAddons.push({
          id: chk.value,
          name: chk.getAttribute('data-name'),
          price: parseFloat(chk.getAttribute('data-price'))
        });
      });
    });

    if (!isValid) return;

    // Proceed to insert into cart
    let finalPrice = tempProductForAddons.basePrice;
    selectedAddons.forEach(a => {
      finalPrice += a.price;
    });

    insertIntoCart(tempProductForAddons.id, tempProductForAddons.name, finalPrice, selectedAddons);
    bootstrap.Modal.getInstance(document.getElementById('selectAddonsModal')).hide();
  }

  function insertIntoCart(serviceId, name, price, addonsArray) {
    let addonsStr = addonsArray.map(a => a.id).sort().join('-');
    let cartKey = serviceId + '-' + addonsStr;

    if (cart[cartKey]) {
      cart[cartKey].qty++;
    } else {
      cart[cartKey] = {
        serviceId: serviceId,
        name: name,
        price: price,
        qty: 1,
        addons: addonsArray
      };
    }
    updateCartUI();
  }

  function changeQty(cartKey, delta) {
    if (!cart[cartKey]) return;
    cart[cartKey].qty += delta;
    if (cart[cartKey].qty <= 0) {
      delete cart[cartKey];
    }
    updateCartUI();
  }

  function updateCartUI() {
    let listStr = "";
    let total = 0;
    let count = 0;

    for (let key in cart) {
      let item = cart[key];
      let itemTotal = item.price * item.qty;
      total += itemTotal;
      count++;

      let addonsText = '';
      if (item.addons.length > 0) {
        addonsText = `<div class="text-muted" style="font-size: 0.75rem;">+ ` + item.addons.map(a => a.name).join(', ') + `</div>`;
      }

      listStr += `
            <li class="list-group-item d-flex justify-content-between align-items-center px-3 py-2">
                <div class="d-flex flex-column" style="width: 60%;">
                    <span class="text-truncate fw-semibold" title="${item.name}">${item.name}</span>
                    ${addonsText}
                    <small class="text-muted">R$ ${item.price.toFixed(2).replace('.', ',')}</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-xs btn-icon btn-outline-secondary" onclick="changeQty('${key}', -1)">
                        <i class="ti tabler-minus fs-6"></i>
                    </button>
                    <span class="fw-bold px-1">${item.qty}</span>
                    <button class="btn btn-xs btn-icon btn-outline-primary" onclick="changeQty('${key}', 1)">
                        <i class="ti tabler-plus fs-6"></i>
                    </button>
                </div>
            </li>
            `;
    }

    const listEl = document.getElementById('cart-items');
    const emptyMsg = document.getElementById('empty-cart-msg');
    const btnCheckout = document.getElementById('btn-checkout');

    if (count > 0) {
      listEl.innerHTML = listStr;
      listEl.style.display = 'block';
      emptyMsg.style.display = 'none';
      btnCheckout.disabled = false;
    } else {
      listEl.style.display = 'none';
      emptyMsg.style.display = 'block';
      btnCheckout.disabled = true;
    }

    document.getElementById('cart-total').innerText = 'R$ ' + total.toFixed(2).replace('.', ',');
  }

  // Busca de produtos simples
  document.getElementById('searchProduct')?.addEventListener('input', function(e) {
    let query = e.target.value.toLowerCase();
    document.querySelectorAll('.product-item').forEach(item => {
      if (item.getAttribute('data-name').includes(query)) {
        item.style.display = 'block';
      } else {
        item.style.display = 'none';
      }
    });
  });

  // Exibir/Ocultar Cliente se for fiado
  document.getElementById('checkout-payment-method')?.addEventListener('change', function(e) {
    if (e.target.value === 'fiado') {
      document.getElementById('client-select-wrapper').style.display = 'block';
    } else {
      document.getElementById('client-select-wrapper').style.display = 'none';
    }
  });

  function openCheckout() {
    let total = 0;
    for (let id in cart) {
      total += cart[id].price * cart[id].qty;
    }
    document.getElementById('checkout-total').innerText = 'R$ ' + total.toFixed(2).replace('.', ',');

    var myModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
    myModal.show();
  }

  function processCheckout() {
    const btn = document.getElementById('btn-confirm-checkout');
    const spinner = btn.querySelector('.spinner-border');
    const method = document.getElementById('checkout-payment-method').value;
    const clientId = document.getElementById('checkout-client').value || null;

    if (method === 'fiado' && !clientId) {
      alert("Para vender no Fiado, você deve selecionar um cliente (Futuramente implementado).");
      return;
    }

    // Preparar payload
    let itemsPayload = [];
    for (let key in cart) {
      let i = cart[key];
      let addonsIds = i.addons.map(a => a.id);

      itemsPayload.push({
        id: i.serviceId,
        qty: i.qty,
        addons: addonsIds
      });
    }

    btn.disabled = true;
    spinner.classList.remove('d-none');

    // Obter ID do caixa inserido ali em cima (no hidden span index.blade.php)
    const registerId = document.getElementById('current-register-id').value;
    if (!registerId) {
      alert("Erro: Nenhum caixa aberto detectado.");
      return;
    }

    fetch(`/cash-register/${registerId}/checkout`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          items: itemsPayload,
          payment_method: method,
          client_id: clientId
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Sucesso!
          cart = {}; // Limpar carrinho
          updateCartUI();
          bootstrap.Modal.getInstance(document.getElementById('checkoutModal')).hide();

          // Mostrar alerta bonito ou reload p/ atualizar Saldo
          window.location.reload();
        } else {
          alert("Erro ao finalizar venda: " + data.message);
          btn.disabled = false;
          spinner.classList.add('d-none');
        }
      })
      .catch(error => {
        console.error(error);
        alert("Erro na requisição. Tente novamente.");
        btn.disabled = false;
        spinner.classList.add('d-none');
      });
  }
</script>
@endsection