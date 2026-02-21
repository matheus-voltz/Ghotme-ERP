@extends('layouts/layoutMaster')

@section('title', 'Faturamento e Planos')

@section('vendor-style')
@vite([
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss',
'resources/assets/vendor/libs/animate-css/animate.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'
])
<style>
  .credit-card-visual {
    perspective: 1000px;
    width: 100%;
    max-width: 400px;
    height: 220px;
    margin: 0 auto;
    position: relative;
  }

  .cc-card-inner {
    position: relative;
    width: 100%;
    height: 100%;
    text-align: center;
    transition: transform 0.6s;
    transform-style: preserve-3d;
    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
    border-radius: 15px;
  }

  .credit-card-visual.flipped .cc-card-inner {
    transform: rotateY(180deg);
  }

  .cc-front,
  .cc-back {
    position: absolute;
    width: 100%;
    height: 100%;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    border-radius: 15px;
    padding: 25px;
    color: white;
    background: linear-gradient(135deg, #7367f0 0%, #4844a3 100%);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .cc-back {
    transform: rotateY(180deg);
    background: linear-gradient(135deg, #4844a3 0%, #7367f0 100%);
  }

  .cc-strip {
    background: #000;
    height: 40px;
    width: 100%;
    position: absolute;
    top: 20px;
    left: 0;
  }

  .cc-cvv-box {
    background: #fff;
    color: #000;
    height: 35px;
    width: 80%;
    margin-top: 60px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding-right: 10px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 1.2rem;
  }

  .cc-chip {
    width: 50px;
    height: 35px;
    background: linear-gradient(135deg, #ffd700 0%, #b8860b 100%);
    border-radius: 5px;
  }

  .cc-number {
    font-size: 1.5rem;
    letter-spacing: 2px;
    text-align: left;
    margin-bottom: 20px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
  }
</style>
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
'resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'
])
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const finalBaseUrl = (typeof baseUrl !== 'undefined') ? baseUrl : window.location.origin + '/';

    // Máscara CPF/CNPJ
    $('.cpf-cnpj-mask').on('input', function() {
      let v = this.value.replace(/\D/g, '');
      if (v.length > 11) {
        v = v.slice(0, 2) + '.' + v.slice(2, 5) + '.' + v.slice(5, 8) + '/' + v.slice(8, 12) + '-' + v.slice(12, 14);
      } else {
        v = v.slice(0, 3) + '.' + v.slice(3, 6) + '.' + v.slice(6, 9) + '-' + v.slice(9, 11);
      }
      this.value = v;
    });

    // Alternar abas
    const radios = document.querySelectorAll('input[name="collapsible-payment"]');
    radios.forEach(radio => {
      radio.addEventListener('change', function() {
        document.querySelectorAll('.payment-section').forEach(el => el.classList.add('d-none'));
        document.getElementById('payment-' + this.value).classList.remove('d-none');
      });
    });

    // Visual Card Logic
    const visualCard = document.getElementById('visualCard');
    const ccNumberInput = document.getElementById('paymentCard');
    const ccNameInput = document.getElementById('paymentName');
    const ccExpiryInput = document.getElementById('paymentExpiryDate');
    const ccCvvInput = document.getElementById('paymentCvv');

    if (ccNumberInput) {
      ccNumberInput.addEventListener('input', (e) => {
        let v = e.target.value.replace(/\D/g, '').substring(0, 16);
        let f = v.match(/.{1,4}/g)?.join(' ') || '';
        e.target.value = f;
        document.querySelector('.cc-number').textContent = f || '#### #### #### ####';
      });
      ccNameInput.addEventListener('input', (e) => {
        document.querySelector('.cc-holder-name').textContent = e.target.value.toUpperCase() || 'TITULAR DO CARTÃO';
      });
      ccExpiryInput.addEventListener('input', (e) => {
        let v = e.target.value.replace(/\D/g, '').substring(0, 4);
        if (v.length >= 2) v = v.substring(0, 2) + '/' + v.substring(2, 4);
        e.target.value = v;
        document.querySelector('.cc-expiry-date').textContent = v || 'MM/AA';
      });
      ccCvvInput.addEventListener('focus', () => visualCard.classList.add('flipped'));
      ccCvvInput.addEventListener('blur', () => visualCard.classList.remove('flipped'));
      ccCvvInput.addEventListener('input', (e) => {
        document.querySelector('.cc-cvv-box').textContent = e.target.value || '***';
      });
    }

    // AJAX Payment Logic
    $(document).on('click', '.btn-generate-payment', function(e) {
      e.preventDefault();
      const $btn = $(this);
      const method = $btn.data('method');
      const originalText = $btn.html();

      if (!$('input[name="cpf_cnpj"]').val()) {
        Swal.fire('Dados Incompletos', 'Preencha seu CPF/CNPJ abaixo.', 'warning').then(() => $('input[name="cpf_cnpj"]').focus());
        return;
      }

      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processando...');

      const payload = {
        method: method
      };
      if (method === 'credit_card') {
        payload.card_number = document.getElementById('paymentCard').value;
        payload.card_name = document.getElementById('paymentName').value;
        payload.card_expiry = document.getElementById('paymentExpiryDate').value;
        payload.card_cvv = document.getElementById('paymentCvv').value;

        const installmentsContainer = document.getElementById('installments-container');
        if (installmentsContainer && !installmentsContainer.classList.contains('d-none')) {
          payload.installments = document.getElementById('paymentInstallments').value;
        }
      }

      $.ajax({
        url: finalBaseUrl + 'settings/generate-payment',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
          $btn.prop('disabled', false).html(originalText);
          if (data.success) {
            if (method === 'pix') {
              $('#pix-content-area').html(`
                <div class="mt-4 p-4 border rounded bg-white shadow-sm animate__animated animate__fadeIn text-center">
                    <h5 class="text-success mb-3">Pix Gerado!</h5>
                    <div class="mb-3"><img src="data:image/png;base64,${data.pix_qr}" class="img-fluid border p-2" style="max-width: 200px"></div>
                    <div class="mb-3 text-start mx-auto" style="max-width: 400px;">
                        <div class="input-group"><input type="text" class="form-control" value="${data.pix_code}" id="pixCopyInput" readonly><button class="btn btn-primary" type="button" onclick="window.copyPixCode()"><i class="ti tabler-copy"></i> Copiar</button></div>
                    </div>
                    <button class="btn btn-link btn-sm text-muted" onclick="window.resetPix()">Trocar método</button>
                </div>`);
              Swal.fire({
                icon: 'success',
                title: 'PIX Pronto!',
                timer: 1500,
                showConfirmButton: false
              });
            } else if (method === 'boleto') {
              window.open(data.bank_slip_url, '_blank');
              Swal.fire('Boleto Gerado', 'O boleto foi aberto em uma nova aba.', 'success');
            } else {
              // Cartão de Crédito: Processamento Direto
              if (data.status === 'paid') {
                Swal.fire({
                  icon: 'success',
                  title: 'Pagamento Confirmado!',
                  text: 'Seu plano foi ativado com sucesso.',
                  timer: 2000,
                  showConfirmButton: false
                }).then(() => location.reload());
              } else {
                Swal.fire({
                  icon: 'success',
                  title: 'Cobrança Gerada',
                  text: 'Seu pagamento está sendo processado.',
                  timer: 2000,
                  showConfirmButton: false
                }).then(() => location.reload());
              }
            }
          } else {
            Swal.fire('Erro', data.message, 'error');
          }
        },
        error: function() {
          $btn.prop('disabled', false).html(originalText);
          Swal.fire('Erro', 'Falha na comunicação com o Asaas.', 'error');
        }
      });
    });

    window.copyPixCode = function() {
      const input = document.getElementById("pixCopyInput");
      input.select();
      navigator.clipboard.writeText(input.value);
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Copiado!',
        showConfirmButton: false,
        timer: 1500
      });
    };

    window.resetPix = function() {
      $('#pix-content-area').html(`<button type="button" class="btn btn-success btn-lg btn-generate-payment" data-method="pix">Gerar Pix</button>`);
    };

    // Plan Selection Logic
    $('.btn-upgrade-plan').on('click', function() {
      const $btn = $(this);
      const planId = $btn.data('plan');
      const isYearly = $('.price-duration-toggler').is(':checked');
      const planType = isYearly ? 'yearly' : 'monthly';
      const originalText = $btn.html();

      $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

      $.ajax({
        url: finalBaseUrl + 'settings/select-plan',
        method: 'POST',
        data: JSON.stringify({
          plan: planId,
          type: planType
        }),
        contentType: 'application/json',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
          $btn.prop('disabled', false).html(originalText);
          if (data.success) {
            $('#pricingModal').modal('hide');

            // Update UI dynamically
            const planNameMap = {
              'padrao': 'Padrão',
              'enterprise': 'Enterprise'
            };
            const planNameDisplay = planNameMap[planId] || planId;
            const typeDisplay = isYearly ? 'Anual' : 'Mensal';

            // Populating Installments correctly
            const instContainer = $('#installments-container');
            if (isYearly) {
              instContainer.removeClass('d-none');
              let planValue = planId === 'enterprise' ? 2790 : 1490; // Assuming these are the yearly prices in cents/smallest unit
              let instOptions = '';
              for (let i = 1; i <= 12; i++) {
                let val = (planValue / i).toLocaleString('pt-BR', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                });
                instOptions += `<option value="${i}">${i}x de R$ ${val}</option>`;
              }
              $('#paymentInstallments').html(instOptions);
            } else {
              instContainer.addClass('d-none');
            }

            // Show alert in payment methods section
            let alertHtml = '';
            if (isYearly) {
              alertHtml = `<div class="alert alert-info alert-dismissible d-flex align-items-center mb-4" role="alert">
                  <i class="ti tabler-info-circle me-2"></i>
                  <div>Você selecionou o plano <strong>${planNameDisplay}</strong> no formato <strong>${typeDisplay}</strong>. A cobrança será <strong>À Vista</strong> (Pagamento Único).</div>
                  <button type="button" class="btn-close btn-cancel-plan" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
            } else {
              alertHtml = `<div class="alert alert-primary alert-dismissible d-flex align-items-center mb-4" role="alert">
                  <i class="ti tabler-calendar me-2"></i>
                  <div>Você selecionou o plano <strong>${planNameDisplay}</strong> no formato <strong>${typeDisplay}</strong>. A cobrança funciona via <strong>Assinatura Recorrente Mensal</strong>.</div>
                  <button type="button" class="btn-close btn-cancel-plan" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;
            }

            // Remove previous alerts if exist, then prepend
            $('#dynamic-plan-alert').remove();
            $('.btn-group').parent().parent().before(`<div id="dynamic-plan-alert" class="row justify-content-center"><div class="col-md-12">${alertHtml}</div></div>`);

            // Scroll to payment methods
            $('html, body').animate({
              scrollTop: $("#dynamic-plan-alert").offset().top - 100
            }, 500);

            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Pronto! Prossiga abaixo.',
              showConfirmButton: false,
              timer: 3000,
              width: '280px',
              padding: '10px',
              customClass: {
                title: 'fs-6 fw-normal mb-0'
              }
            });

          } else {
            Swal.fire('Erro', data.message, 'error');
          }
        },
        error: function() {
          $btn.prop('disabled', false).html(originalText);
          Swal.fire('Erro', 'Ocorreu um erro ao selecionar o plano.', 'error');
        }
      });
    });

    // Cancel Plan Logic
    $(document).on('click', '.btn-cancel-plan', function(e) {
      // Remover container de parcelas imediatamente na UI
      $('#installments-container').addClass('d-none');
      $('#dynamic-plan-alert').fadeOut(300, function() {
        $(this).remove();
      });
      $('.alert-warning.alert-dismissible').fadeOut(300, function() {
        $(this).remove();
      });

      $.ajax({
        url: finalBaseUrl + 'settings/cancel-plan',
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
          if (data.success) {
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'info',
              title: 'Plano removido.',
              showConfirmButton: false,
              timer: 2000
            });
          }
        }
      });
    });

    $('#btnSaveProfile').on('click', function() {
      const $form = $('#formAccountSettings');
      $.ajax({
        url: "{{ route('settings.update-profile') }}",
        method: 'POST',
        data: $form.serialize(),
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
          Swal.fire({
            icon: 'success',
            title: 'Perfil Atualizado!'
          });
        }
      });
    });
  });
</script>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <!-- Current Plan -->
    <div class="card mb-6 {{ $user->plan != 'free' ? 'border border-primary border-2 shadow-lg' : '' }}">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Plano atual</h5>
        @if($user->plan != 'free')
        <span class="badge bg-label-primary rounded-pill"><i class="ti tabler-star me-1"></i> VIP</span>
        @endif
      </div>
      <div class="card-body">
        <div class="row align-items-center {{ $user->plan != 'free' ? 'bg-label-primary p-4 rounded' : '' }}">
          <div class="{{ $user->plan != 'free' ? 'col-md-8 text-center text-md-start' : 'col-md-6' }}">
            <h6 class="{{ $user->plan != 'free' ? 'text-primary fs-4 mb-2' : '' }}">Seu plano é {{ $planDetails['name'] }}</h6>
            <p>{{ $planDetails['description'] }}</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pricingModal"><i class="ti tabler-rocket me-1"></i> {{ $user->plan != 'free' ? 'Alterar Plano' : 'Escolher um Plano' }}</button>
          </div>

          @if($user->plan === 'free')
          <div class="col-md-6">
            <div class="d-flex justify-content-between mb-1">
              <span>Dias de Uso</span>
              <span>{{ $daysUsed }} de 30</span>
            </div>
            <div class="progress" style="height: 10px;">
              <div class="progress-bar" style="width: {{ ($daysUsed/30)*100 }}%"></div>
            </div>
          </div>
          @else
          <div class="col-md-4 d-none d-md-flex justify-content-end">
            <i class="ti tabler-rosette-discount-check-filled text-primary" style="font-size: 5rem;"></i>
          </div>
          @endif
        </div>

        @if($selectedPlanDetails)
        <div class="alert alert-warning alert-dismissible mt-4 mb-0" role="alert">
          <button type="button" class="btn-close btn-cancel-plan" data-bs-dismiss="alert" aria-label="Close"></button>
          <h6 class="alert-heading mb-1"><i class="ti tabler-alert-circle me-1"></i> Finalize a contratação!</h6>
          <p class="mb-0">Você escolheu o plano <strong>{{ $selectedPlanDetails['name'] }} ({{ $selectedPlanDetails['type'] }})</strong> por <strong>R$ {{ $selectedPlanDetails['amount'] }}</strong>. Escolha um método de pagamento abaixo para ativar.</p>
        </div>
        @endif
      </div>
    </div>

    @if($user->plan === 'free' || isset($selectedPlanDetails))
    <!-- Payment Methods -->
    <div class="card mb-6" id="payment-methods-card">
      <h5 class="card-header">Métodos de Pagamento</h5>
      <div class="card-body">

        @if($selectedPlanDetails)
        <div id="dynamic-plan-alert" class="row justify-content-center">
          <div class="col-md-12">
            @if($selectedPlanDetails['type'] === 'Anual')
            <div class="alert alert-info alert-dismissible d-flex align-items-center mb-4" role="alert">
              <i class="ti tabler-info-circle me-2"></i>
              <div>Você selecionou o plano <strong>{{ $selectedPlanDetails['name'] }}</strong> no formato <strong>{{ $selectedPlanDetails['type'] }}</strong>. A cobrança será <strong>À Vista</strong> (Pagamento Único).</div>
              <button type="button" class="btn-close btn-cancel-plan" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @else
            <div class="alert alert-primary alert-dismissible d-flex align-items-center mb-4" role="alert">
              <i class="ti tabler-calendar me-2"></i>
              <div>Você selecionou o plano <strong>{{ $selectedPlanDetails['name'] }}</strong> no formato <strong>{{ $selectedPlanDetails['type'] }}</strong>. A cobrança funciona via <strong>Assinatura Recorrente Mensal</strong>.</div>
              <button type="button" class="btn-close btn-cancel-plan" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
          </div>
        </div>
        @endif

        <div class="row justify-content-center mb-6">
          <div class="col-md-8 text-center">
            <div class="btn-group w-100">
              <input type="radio" class="btn-check" name="collapsible-payment" id="pmCC" value="cc" checked>
              <label class="btn btn-outline-primary" for="pmCC"><i class="ti tabler-credit-card me-2"></i>Cartão</label>
              <input type="radio" class="btn-check" name="collapsible-payment" id="pmPix" value="pix">
              <label class="btn btn-outline-primary" for="pmPix"><i class="ti tabler-qrcode me-2"></i>Pix</label>
              <input type="radio" class="btn-check" name="collapsible-payment" id="pmBoleto" value="boleto">
              <label class="btn btn-outline-primary" for="pmBoleto"><i class="ti tabler-barcode me-2"></i>Boleto</label>
            </div>
          </div>
        </div>

        <!-- Credit Card -->
        <div id="payment-cc" class="payment-section">
          <div class="row g-6">
            <div class="col-md-5 d-flex align-items-center justify-content-center">
              <div class="credit-card-visual" id="visualCard">
                <div class="cc-card-inner">
                  <div class="cc-front">
                    <div class="cc-chip"></div>
                    <div class="cc-number">#### #### #### ####</div>
                    <div class="d-flex justify-content-between">
                      <div><small class="d-block opacity-75">TITULAR</small><span class="cc-holder-name">NOME NO CARTÃO</span></div>
                      <div><small class="d-block opacity-75">VALIDADE</small><span class="cc-expiry-date">MM/AA</span></div>
                    </div>
                  </div>
                  <div class="cc-back">
                    <div class="cc-strip"></div>
                    <div class="cc-cvv-box">***</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-7">
              <form id="creditCardForm" class="row g-4">
                <div class="col-12">
                  <label class="form-label">Número do Cartão</label>
                  <input type="text" id="paymentCard" class="form-control" placeholder="0000 0000 0000 0000" />
                </div>
                <div class="col-12">
                  <label class="form-label">Nome no Cartão</label>
                  <input type="text" id="paymentName" class="form-control" placeholder="JOÃO DA SILVA" />
                </div>
                <div class="col-6">
                  <label class="form-label">Validade</label>
                  <input type="text" id="paymentExpiryDate" class="form-control" placeholder="MM/AA" />
                </div>
                <div class="col-6">
                  <label class="form-label">CVV</label>
                  <input type="text" id="paymentCvv" class="form-control" placeholder="123" maxlength="4" />
                </div>
                <!-- Parcelas (Aparece Apenas no Anual) -->
                <div class="col-12 {{ isset($selectedPlanDetails) && $selectedPlanDetails['type'] === 'Anual' ? '' : 'd-none' }}" id="installments-container">
                  <label class="form-label">Parcelamento</label>
                  <select id="paymentInstallments" class="form-select">
                    @if(isset($selectedPlanDetails) && $selectedPlanDetails['type'] === 'Anual')
                    @php
                    $planValue = $selectedPlanDetails['name'] === 'Enterprise' ? 2790 : 1490;
                    @endphp
                    @for($i=1; $i<=12; $i++)
                      <option value="{{$i}}">{{$i}}x de R$ {{ number_format($planValue / $i, 2, ',', '.') }}</option>
                      @endfor
                      @endif
                  </select>
                </div>
                <div class="col-12">
                  <button type="button" class="btn btn-primary w-100 btn-generate-payment" data-method="credit_card">Pagar com Cartão</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Pix -->
        <div id="payment-pix" class="payment-section d-none text-center p-6 border rounded bg-label-secondary">
          <h4>Pagamento via Pix</h4>
          <p>Aprovação instantânea.</p>
          <div id="pix-content-area">
            <button type="button" class="btn btn-success btn-lg btn-generate-payment" data-method="pix">Gerar Pix</button>
          </div>
        </div>

        <!-- Boleto -->
        <div id="payment-boleto" class="payment-section d-none text-center p-6 border rounded bg-label-secondary">
          <h4>Pagamento via Boleto</h4>
          <button type="button" class="btn btn-info btn-lg btn-generate-payment" data-method="boleto">Gerar Boleto</button>
        </div>
      </div>
    </div>
    @endif

    <!-- Billing Address -->
    <div class="card mb-6">
      <h5 class="card-header">Dados de Cobrança</h5>
      <div class="card-body">
        <form id="formAccountSettings">
          @csrf
          <div class="row g-4">
            <div class="col-md-6"><label class="form-label">Nome da Empresa</label><input type="text" name="companyName" class="form-control" value="{{ $user->company }}"></div>
            <div class="col-md-6"><label class="form-label">CPF ou CNPJ</label><input type="text" name="cpf_cnpj" class="form-control cpf-cnpj-mask" value="{{ $user->cpf_cnpj }}"></div>
            <div class="col-md-6"><label class="form-label">E-mail</label><input type="email" name="billingEmail" class="form-control" value="{{ $user->email }}"></div>
            <div class="col-md-6"><label class="form-label">Celular</label><input type="text" name="mobileNumber" class="form-control" value="{{ $user->contact_number }}"></div>
          </div>
          <button type="button" id="btnSaveProfile" class="btn btn-primary mt-4">Salvar Dados</button>
        </form>
      </div>
    </div>

    <!-- History -->
    <div class="card">
      <h5 class="card-header">Histórico</h5>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Plano</th>
              <th>Valor</th>
              <th>Status</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody>
            @foreach($billingHistory as $h)
            <tr>
              <td>{{ $h->plan_name }}</td>
              <td>R$ {{ number_format($h->amount, 2, ',', '.') }}</td>
              <td><span class="badge bg-label-primary">{{ $h->status }}</span></td>
              <td>{{ $h->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@include('_partials/_modals/modal-pricing')
@endsection