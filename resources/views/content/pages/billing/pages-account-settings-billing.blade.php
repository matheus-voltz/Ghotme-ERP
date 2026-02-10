@extends('layouts/layoutMaster')

@section('title', 'Account settings - Pages')

<!-- Vendor Styles -->
@section('vendor-style')
@vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss', 'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss', 'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss', 'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js', 'resources/assets/vendor/libs/@form-validation/bootstrap5.js', 'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleave-zen/cleave-zen.js', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.js', 'resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
@vite(['resources/assets/js/pages-pricing.js', 'resources/assets/js/modal-edit-cc.js'])
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const $form = $('#formAccountSettings');
    const $btnSave = $('#btnSaveProfile');

    // Masks logic
    $('.zip-code-mask').on('input', function() {
      let v = this.value.replace(/\D/g, '');
      if (v.length > 5) v = v.slice(0, 5) + '-' + v.slice(5, 8);
      this.value = v;
    });

    $('.phone-mask').on('input', function() {
      let v = this.value.replace(/\D/g, '');
      if (v.length > 10) v = v.slice(0, 2) + ' ' + v.slice(2, 7) + '-' + v.slice(7, 11);
      else if (v.length > 6) v = v.slice(0, 2) + ' ' + v.slice(2, 6) + '-' + v.slice(6, 10);
      else if (v.length > 2) v = v.slice(0, 2) + ' ' + v.slice(2);
      this.value = v;
    });

    $('.cpf-cnpj-mask').on('input', function() {
      let v = this.value.replace(/\D/g, '');
      if (v.length > 11) { // CNPJ
        v = v.slice(0, 2) + '.' + v.slice(2, 5) + '.' + v.slice(5, 8) + '/' + v.slice(8, 12) + '-' + v.slice(12, 14);
      } else if (v.length > 0) { // CPF
        v = v.slice(0, 3) + '.' + v.slice(3, 6) + '.' + v.slice(6, 9) + '-' + v.slice(9, 11);
      }
      this.value = v;
    });

    // SAVE PROFILE
    $btnSave.on('click', function(e) {
      e.preventDefault();
      const originalText = $btnSave.html();
      $btnSave.html('<span class="spinner-border spinner-border-sm" role="status"></span>').prop('disabled', true);

      $.ajax({
        url: "{{ route('settings.update-profile') }}",
        method: 'POST',
        data: $form.serialize(),
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
          $btnSave.html(originalText).prop('disabled', false);
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Sucesso!',
              text: data.message
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Erro!',
              text: data.message
            });
          }
        },
        error: function() {
          $btnSave.html(originalText).prop('disabled', false);
          Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao salvar perfil.'
          });
        }
      });
    });

    // GENERATE PAYMENT
    let pendingMethod = null;
    const $resultContainer = $('#payment-result-container');
    const $infoDefault = $('#payment-info-default');
    const $resultContent = $('#payment-result-content');

    $('.btn-generate-payment').on('click', function(e) {
      e.preventDefault();
      const $btn = $(this);
      const method = $btn.data('method');
      const originalText = $btn.html();

      // Only force plan selection if no future plan is selected yet
      const currentPlan = "{{ $user->plan }}";
      const selectedPlan = "{{ $user->selected_plan }}";

      if (currentPlan === 'free' && (!selectedPlan || selectedPlan === 'free')) {
        pendingMethod = method;
        Swal.fire({
          icon: 'info',
          title: 'Selecione um Plano',
          text: 'Você precisa primeiro escolher qual plano deseja assinar para gerar a cobrança.',
          confirmButtonText: 'Ver Planos',
          customClass: {
            confirmButton: 'btn btn-primary'
          },
          buttonsStyling: false
        }).then(() => {
          $('#pricingModal').modal('show');
        });
        return;
      }

      // Client-side validation for CPF/CNPJ
      const cpfCnpjValue = $('input[name="cpf_cnpj"]').val().trim();
      if (!cpfCnpjValue) {
        Swal.fire({
          icon: 'warning',
          title: 'Dados Incompletos',
          text: 'Por favor, preencha seu CPF ou CNPJ na seção "Dados de Cobrança" abaixo antes de prosseguir.',
          confirmButtonText: 'Ir para o campo',
          customClass: {
            confirmButton: 'btn btn-primary'
          },
          buttonsStyling: false
        }).then(() => {
          $('input[name="cpf_cnpj"]').focus();
          $('input[name="cpf_cnpj"]')[0].scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });
        });
        return;
      }

      $btn.html('<span class="spinner-border spinner-border-sm" role="status"></span>').prop('disabled', true);

      $.ajax({
        url: "{{ route('settings.generate-payment') }}",
        method: 'POST',
        data: JSON.stringify({
          method: method
        }),
        contentType: 'application/json',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
          $btn.html(originalText).prop('disabled', false);
          if (data.success) {
            $infoDefault.addClass('d-none');
            $resultContainer.removeClass('d-none');
            let html = '';
            if (method === 'pix') {
              html = `<h6 class="mb-4 text-success">Pagamento via PIX</h6><img src="data:image/png;base64,${data.pix_qr}" class="img-fluid mb-4 shadow-sm rounded" style="max-width: 200px"><div class="mb-4"><small class="text-muted d-block mb-2">Copia e Cola:</small><div class="input-group"><input type="text" class="form-control form-control-sm" value="${data.pix_code}" id="pix-code" readonly><button class="btn btn-primary btn-sm" onclick="copyPix()">Copiar</button></div></div><button class="btn btn-link btn-sm text-muted" onclick="showPaymentInfo()"><i class="icon-base ti tabler-arrow-left"></i> Alterar método</button>`;
            } else if (method === 'boleto') {
              html = `<h6 class="mb-4 text-info">Boleto Gerado</h6><div class="p-4 bg-white rounded mb-4 shadow-sm"><i class="icon-base ti tabler-barcode fs-1 text-primary mb-3 d-block"></i><p class="mb-0">Valor: <strong>R$ ${data.amount}</strong></p></div><a href="${data.bank_slip_url}" target="_blank" class="btn btn-primary w-100 mb-3">Imprimir Boleto</a><button class="btn btn-link btn-sm text-muted" onclick="showPaymentInfo()"><i class="icon-base ti tabler-arrow-left"></i> Alterar método</button>`;
            } else {
              html = `<h6 class="mb-4 text-primary">Cartão de Crédito</h6><div class="p-4 bg-white rounded mb-4 shadow-sm"><i class="icon-base ti tabler-credit-card fs-1 text-primary mb-3 d-block"></i><p>Conclua o pagamento no ambiente seguro do Asaas.</p></div><a href="${data.invoice_url || data.redirect_url}" target="_blank" class="btn btn-primary w-100 mb-3">Pagar Agora</a><button class="btn btn-link btn-sm text-muted" onclick="showPaymentInfo()"><i class="icon-base ti tabler-arrow-left"></i> Alterar método</button>`;
            }
            $resultContent.html(html);
            Swal.fire({
              icon: 'success',
              title: 'Cobrança Gerada!',
              timer: 2000,
              showConfirmButton: false
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Erro!',
              text: data.message
            });
          }
        },
        error: function() {
          $btn.html(originalText).prop('disabled', false);
          Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'Erro ao gerar pagamento.'
          });
        }
      });
    });

    // SELECT PLAN (UPGRADE)
    $('.btn-upgrade-plan').on('click', function() {
      const $btn = $(this);
      const plan = $btn.data('plan');
      const type = $('.price-duration-toggler').is(':checked') ? 'yearly' : 'monthly';
      const originalText = $btn.html();

      // Client-side validation for CPF/CNPJ
      const cpfCnpjValue = $('input[name="cpf_cnpj"]').val().trim();
      if (!cpfCnpjValue) {
        $('#pricingModal').modal('hide');
        Swal.fire({
          icon: 'warning',
          title: 'Dados Incompletos',
          text: 'Por favor, preencha seu CPF ou CNPJ na seção "Dados de Cobrança" antes de escolher um plano.',
          confirmButtonText: 'Preencher agora',
          customClass: {
            confirmButton: 'btn btn-primary'
          },
          buttonsStyling: false
        }).then(() => {
          $('input[name="cpf_cnpj"]').focus();
          $('input[name="cpf_cnpj"]')[0].scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });
        });
        return;
      }

      $btn.html('<span class="spinner-border spinner-border-sm" role="status"></span>').prop('disabled', true);

      $.ajax({
        url: "{{ route('settings.select-plan') }}",
        method: 'POST',
        data: JSON.stringify({
          plan: plan,
          type: type,
          method: pendingMethod
        }),
        contentType: 'application/json',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
          if (data.success) {
            location.reload(); // Just reload to show the "Selected" message
          } else {
            $btn.html(originalText).prop('disabled', false);
            Swal.fire({
              icon: 'error',
              title: 'Erro',
              text: data.message
            });
          }
        }
      });
    });
  });

  function showPaymentInfo() {
    $('#payment-result-container').addClass('d-none');
    $('#payment-info-default').removeClass('d-none');
  }

  function copyPix() {
    const copyText = document.getElementById("pix-code");
    copyText.select();
    navigator.clipboard.writeText(copyText.value);
    Swal.fire({
      icon: 'success',
      title: 'Copiado!',
      timer: 1000,
      showConfirmButton: false
    });
  }
</script>
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="nav-align-top">
      <ul class="nav nav-pills flex-column flex-md-row mb-6">
        <li class="nav-item">
          <a class="nav-link" href="{{ route('profile.show') }}"><i class="icon-base ti tabler-user-circle icon-sm me-1_5"></i> Minha Conta</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="{{ route('settings') }}"><i class="icon-base ti tabler-receipt-2 icon-sm me-1_5"></i> Faturamento & Planos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('team-management') }}"><i class="icon-base ti tabler-users icon-sm me-1_5"></i> Gerenciar Equipe</a>
        </li>
      </ul>
    </div>
    <div class="card mb-6">
      <!-- Current Plan -->
      <h5 class="card-header">Plano atual</h5>
      <div class="card-body">
        <div class="row row-gap-6">
          <div class="col-md-6 mb-1">
            <div class="mb-6">
              <h6 class="mb-1">Seu plano atual é {{ $planDetails['name'] }}</h6>
              <p>Gerencie sua assinatura e detalhes do plano.</p>
            </div>
            <div class="mb-6">
              <h6 class="mb-1">Situação: {{ $user->plan === 'free' ? 'Período de Teste' : 'Plano ' . ($user->plan_type === 'yearly' ? 'Anual' : 'Mensal') . ' Ativo' }}</h6>
              <p>{{ $planDetails['description'] }}</p>
            </div>
            <div>
              <h6 class="mb-1"><span class="me-1">R$ {{ $planDetails['price'] }} Por {{ $planDetails['period'] }}</span> <span class="badge bg-label-primary rounded-pill">Plano {{ $planDetails['name'] }}</span></h6>
              @if($user->plan === 'free' && $user->cpf_cnpj && !$selectedPlanDetails)
              <p class="text-primary fw-bold mt-2 animate__animated animate__pulse animate__infinite">
                <i class="icon-base ti tabler-arrow-narrow-right"></i> Selecione um plano para continuar
              </p>
              @endif
              @if($user->plan === 'free' && $selectedPlanDetails)
              <p class="text-success fw-bold mt-2">
                <i class="icon-base ti tabler-check"></i> Plano Selecionado: R$ {{ $selectedPlanDetails['amount'] }} {{ $selectedPlanDetails['type'] }}
              </p>
              @endif
            </div>
          </div>
          <div class="col-md-6">
            @if($trialExpired && $user->plan === 'free')
            <div class="alert alert-danger mb-4" role="alert">
              <h5 class="alert-heading mb-1 d-flex align-items-center">
                <span class="alert-icon rounded"><i class="icon-base ti tabler-alert-circle icon-md"></i></span>
                <span>Teste grátis expirado!</span>
              </h5>
              <span class="ms-11 ps-1">Faça o upgrade para continuar usando o sistema.</span>
            </div>
            @endif

            @if($user->plan === 'free')
            <div class="plan-statistics">
              <div class="d-flex justify-content-between mb-1">
                <h6 class="mb-0">Dias de Uso</h6>
                <h6 class="mb-0">{{ (int)max(0, $daysUsed) }} de 30 Dias</h6>
              </div>
              <div class="progress mb-1" style="height: 8px;">
                @php $percent = min(100, max(0, ((int)$daysUsed / 30) * 100)); @endphp
                <div class="progress-bar {{ $trialExpired ? 'bg-danger' : '' }}" role="progressbar" style="width: {{ $percent }}%"></div>
              </div>
              <small>{{ (int)max(0, $trialDaysLeft) }} dias restantes de teste grátis</small>
            </div>
            @endif
          </div>
          <div class="col-12 d-flex gap-2 flex-wrap">
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#pricingModal"><i class="ti tabler-rocket me-1"></i> Escolher um Plano</button>
          </div>
        </div>
      </div>
      <!-- /Current Plan -->
    </div>
    <div class="card mb-6">
      <h5 class="card-header">Payment Methods</h5>
      <div class="card-body">
        <style>
          /* Visual Credit Card Styles */
          .credit-card-visual {
            perspective: 1000px;
            width: 100%;
            max-width: 400px;
            height: 240px;
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
            padding: 20px;
            color: white;
            background: linear-gradient(135deg, #6610f2 0%, #6f42c1 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
          }

          .cc-back {
            transform: rotateY(180deg);
            background: linear-gradient(135deg, #6f42c1 0%, #6610f2 100%);
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
            margin-top: 50px;
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
            margin-bottom: 20px;
          }

          .cc-number {
            font-size: 1.5rem;
            letter-spacing: 2px;
            text-align: left;
            margin-bottom: 20px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
          }

          .cc-holder,
          .cc-expires {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
          }

          .cc-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            opacity: 0.8;
          }

          .cc-val {
            font-size: 1rem;
            text-transform: uppercase;
            text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.5);
          }
        </style>

        <div class="row gx-6">
          <div class="col-12">

            <!-- Payment Method Selector -->
            <div class="row justify-content-center mb-6">
              <div class="col-md-8 text-center">
                <div class="btn-group w-100" role="group" aria-label="Payment Method">
                  <input type="radio" class="btn-check" name="collapsible-payment" id="pmApiCC" value="cc" checked>
                  <label class="btn btn-outline-primary" for="pmApiCC"><i class="ti tabler-credit-card me-2"></i>Cartão</label>

                  <input type="radio" class="btn-check" name="collapsible-payment" id="pmApiPix" value="pix">
                  <label class="btn btn-outline-primary" for="pmApiPix"><i class="ti tabler-qrcode me-2"></i>Pix</label>

                  <input type="radio" class="btn-check" name="collapsible-payment" id="pmApiBoleto" value="boleto">
                  <label class="btn btn-outline-primary" for="pmApiBoleto"><i class="ti tabler-barcode me-2"></i>Boleto</label>
                </div>
              </div>
            </div>

            <!-- Credit Card Section -->
            <div id="payment-cc" class="payment-section">
              <div class="row">
                <!-- Visual Card -->
                <div class="col-md-5 order-2 order-md-2 mb-4 d-flex align-items-center justify-content-center">
                  <div class="credit-card-visual" id="visualCard">
                    <div class="cc-card-inner">
                      <div class="cc-front">
                        <div class="cc-chip"></div>
                        <div class="cc-number">#### #### #### ####</div>
                        <div class="d-flex justify-content-between">
                          <div class="cc-holder">
                            <span class="cc-label">Titular</span>
                            <span class="cc-val">NOME NO CARTÃO</span>
                          </div>
                          <div class="cc-expires">
                            <span class="cc-label">Validade</span>
                            <span class="cc-val">MM/AA</span>
                          </div>
                        </div>
                        <div style="position: absolute; top: 20px; right: 20px;">
                          <i class="ti tabler-brand-mastercard fs-1 text-white opacity-50"></i>
                        </div>
                      </div>
                      <div class="cc-back">
                        <div class="cc-strip"></div>
                        <div class="cc-cvv-box">***</div>
                        <div class="text-end mt-4 text-white opacity-75 pe-3">
                          <small>CVV</small>
                        </div>
                        <div class="mt-auto text-start opacity-50">
                          <i class="ti tabler-credit-card fs-2"></i>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Form -->
                <div class="col-md-7 order-1 order-md-1">
                  <form id="creditCardForm" class="row g-4" onsubmit="return false">
                    <div class="col-12">
                      <label class="form-label" for="paymentCard">Número do Cartão</label>
                      <div class="input-group input-group-merge">
                        <input id="paymentCard" name="paymentCard" class="form-control credit-card-mask" type="text" placeholder="1356 3215 6548 7898" />
                        <span class="input-group-text cursor-pointer"><i class="ti tabler-credit-card"></i></span>
                      </div>
                    </div>
                    <div class="col-12">
                      <label class="form-label" for="paymentName">Nome no Cartão</label>
                      <input type="text" id="paymentName" class="form-control" placeholder="João da Silva" oninput="this.value = this.value.toUpperCase()" />
                    </div>
                    <div class="col-6">
                      <label class="form-label" for="paymentExpiryDate">Validade</label>
                      <input type="text" id="paymentExpiryDate" class="form-control expiry-date-mask" placeholder="MM/AA" />
                    </div>
                    <div class="col-6">
                      <label class="form-label" for="paymentCvv">CVV</label>
                      <div class="input-group input-group-merge">
                        <input type="text" id="paymentCvv" class="form-control cvv-code-mask" maxlength="3" placeholder="123" />
                        <span class="input-group-text cursor-pointer" id="paymentCvv2"><i class="ti tabler-help-circle" data-bs-toggle="tooltip" title="Código de 3 dígitos no verso"></i></span>
                      </div>
                    </div>
                    <div class="col-12 mt-4">
                      <button type="submit" class="btn btn-primary w-100 btn-generate-payment" data-method="credit_card">
                        <i class="ti tabler-lock me-1"></i> Pagar com Cartão
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Pix Section -->
            <div id="payment-pix" class="payment-section d-none">
              <div class="text-center p-6 border rounded bg-label-secondary">
                <div class="mb-4">
                  <div class="avatar avatar-xl bg-white p-2 rounded-circle shadow-sm mx-auto mb-3">
                    <i class="ti tabler-qrcode fs-1 text-success"></i>
                  </div>
                  <h4>Pagamento via Pix</h4>
                  <p class="text-muted">Aprovação imediata. Escaneie o QR Code ou copie o código abaixo.</p>
                </div>

                <div id="pix-content-area">
                  <button type="button" class="btn btn-success btn-lg btn-generate-payment" data-method="pix">
                    <i class="ti tabler-qrcode me-2"></i> Gerar Pix
                  </button>
                </div>
              </div>
            </div>

            <!-- Boleto Section -->
            <div id="payment-boleto" class="payment-section d-none">
              <div class="text-center p-6 border rounded bg-label-secondary">
                <div class="mb-4">
                  <div class="avatar avatar-xl bg-white p-2 rounded-circle shadow-sm mx-auto mb-3">
                    <i class="ti tabler-barcode fs-1 text-info"></i>
                  </div>
                  <h4>Pagamento via Boleto</h4>
                  <p class="text-muted">Vencimento em 3 dias úteis. Ao clicar, o boleto abrirá em nova aba.</p>
                </div>

                <button type="button" class="btn btn-info btn-lg btn-generate-payment" data-method="boleto">
                  <i class="ti tabler-printer me-2"></i> Gerar Boleto
                </button>
              </div>
            </div>

          </div>
        </div>

        <script>
          document.addEventListener('DOMContentLoaded', function() {
            // Toggling Sections
            const radios = document.querySelectorAll('input[name="collapsible-payment"]');
            const sections = {
              'cc': document.getElementById('payment-cc'),
              'pix': document.getElementById('payment-pix'),
              'boleto': document.getElementById('payment-boleto')
            };

            radios.forEach(radio => {
              radio.addEventListener('change', function() {
                Object.values(sections).forEach(el => el && el.classList.add('d-none'));
                if (sections[this.value]) {
                  sections[this.value].classList.remove('d-none');
                }
              });
            });

            // Visual Card Logic
            const visualCard = document.getElementById('visualCard');
            const ccNumberInput = document.getElementById('paymentCard');
            const ccNameInput = document.getElementById('paymentName');
            const ccExpiryInput = document.getElementById('paymentExpiryDate');
            const ccCvvInput = document.getElementById('paymentCvv');

            const visualNumber = visualCard.querySelector('.cc-number');
            const visualName = visualCard.querySelector('.cc-holder .cc-val');
            const visualExpiry = visualCard.querySelector('.cc-expires .cc-val');
            const visualCvv = visualCard.querySelector('.cc-cvv-box');
            const visualBrandIcon = visualCard.querySelector('.cc-front .ti'); // Target the brand icon
            const inputBrandIcon = ccNumberInput.nextElementSibling.querySelector('i'); // Target input icon

            // Card Brand Detection Helper
            // Card Brand Detection Helper
            function getCardType(number) {
              const patterns = {
                'visa': /^4/,
                'mastercard': /^5[1-5]|^2[2-7]/,
                'amex': /^3[47]/,
                'discover': /^6(?:011|5)/,
                'diners': /^3(?:0[0-5]|[68])/,
                'jcb': /^(?:2131|1800|35)/,
                'elo': /^4011|438935|45(1416|76|7393)|50(4175|6699|67|90[4-7])|63(6297|6368|6369)/,
                'hipercard': /^(606282\d{10}(\d{3})?)|(3841\d{15})/
              };
              for (let brand in patterns) {
                if (patterns[brand].test(number)) {
                  return brand;
                }
              }
              return 'credit-card'; // Default
            }

            // Input Formatting & Limits
            ccNumberInput.addEventListener('input', (e) => {
              let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
              let formattedValue = '';
              for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                  formattedValue += ' ';
                }
                formattedValue += value[i];
              }
              e.target.value = formattedValue.substring(0, 19); // Limit chars

              // Update Visual Card Number
              let val = e.target.value || '#### #### #### ####';
              visualNumber.textContent = val;

              // Detect Brand
              const brand = getCardType(value);

              // Map brand to Image URL (Using Icons8 CDN for reliability as local assets are missing)
              let imgUrl = 'https://img.icons8.com/color/48/000000/bank-card-back-side.png'; // Default
              if (brand === 'visa') imgUrl = 'https://img.icons8.com/color/48/000000/visa.png';
              else if (brand === 'mastercard') imgUrl = 'https://img.icons8.com/color/48/000000/mastercard.png';
              else if (brand === 'amex') imgUrl = 'https://img.icons8.com/color/48/000000/amex.png';
              else if (brand === 'discover') imgUrl = 'https://img.icons8.com/color/48/000000/discover.png';
              else if (brand === 'elo') imgUrl = 'https://img.icons8.com/color/48/000000/elo.png';
              else if (brand === 'hipercard') imgUrl = 'https://img.icons8.com/color/48/000000/hipercard.png';

              // Update Visual Icon
              const visualIconContainer = visualCard.querySelector('.cc-front div[style*="position: absolute"]');
              if (visualIconContainer) {
                visualIconContainer.innerHTML = `<img src="${imgUrl}" alt="${brand}" height="32">`;
              }

              // Update Input Icon
              // Input icon usually expects a font class, but we can replace the i with img or just leave it generic.
              // Let's replace the i with an img for consistency if possible, or just keep generic font icon.
              // User asked for "bandeira no cartão" (on the card), so updating the visual card is priority.
              // For input, let's keep it clean or try to inject img.
              if (inputBrandIcon) {
                // inputBrandIcon is an <i>. replacing it might break layout if not careful.
                // Let's try to set style background image or just keep generic icon text-muted.
                // Actually, replacing innerHTML of the span wrapper is better.
                const inputIconWrapper = ccNumberInput.nextElementSibling;
                if (inputIconWrapper) {
                  inputIconWrapper.innerHTML = `<img src="${imgUrl}" alt="${brand}" width="24">`;
                }
              }
            });

            // 2. Expiry Date Formatting (MM/AA, max 5 chars)
            ccExpiryInput.addEventListener('input', (e) => {
              let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
              if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
              }
              e.target.value = value.substring(0, 5); // Limit to 5 chars (MM/AA)

              visualExpiry.textContent = e.target.value || 'MM/AA';
            });

            // 3. CVV Formatting (Digits only, max 3 or 4 chars)
            ccCvvInput.addEventListener('input', (e) => {
              let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
              e.target.value = value.substring(0, 4); // Allow up to 4 for Amex, though UI says 3. Safer to allow 4.

              visualCvv.textContent = e.target.value || '***';
            });

            // AJAX Payment Logic (Re-implemented)
            $(document).on('click', '.btn-generate-payment', function(e) { // Use delegated event for dynamic buttons
              e.preventDefault();
              const $btn = $(this);
              const method = $btn.data('method');
              const originalText = $btn.html();

              // Basic Check
              const selectedPlan = "{{ $user->selected_plan }}";
              const currentPlan = "{{ $user->plan }}";

              if (method !== 'credit_card') { // Allow CC update even if free? logic is debatable but following prev flow
                if (currentPlan === 'free' && (!selectedPlan || selectedPlan === 'free')) {
                  Swal.fire({
                    icon: 'info',
                    title: 'Selecione um Plano',
                    text: 'Escolha um plano antes de gerar o pagamento.',
                    confirmButtonText: 'Ver Planos'
                  }).then((result) => {
                    if (result.isConfirmed) $('#pricingModal').modal('show');
                  });
                  return;
                }
              }

              // CPF Check
              const cpfCnpjValue = $('input[name="cpf_cnpj"]').val().trim();
              if (!cpfCnpjValue) {
                Swal.fire('Dados Incompletos', 'Preencha seu CPF/CNPJ acima.', 'warning').then(() => {
                  $('input[name="cpf_cnpj"]').focus();
                });
                return;
              }

              $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processando...');

              $.ajax({
                url: "{{ route('settings.generate-payment') }}",
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                  method: method
                }),
                headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                  $btn.prop('disabled', false).html(originalText);

                  if (data.success) {
                    if (method === 'pix') {
                      // Inject QR Code
                      const pixHtml = `
                                    <div class="mt-4 animate__animated animate__fadeIn">
                                        <h5 class="text-success mb-3">Pix Gerado com Sucesso!</h5>
                                        <img src="data:image/png;base64,${data.pix_qr}" class="img-fluid rounded border p-2 mb-3" style="max-width: 200px">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" value="${data.pix_code}" id="pixCopy" readonly>
                                            <button class="btn btn-outline-primary" type="button" onclick="copyPixCode()"><i class="ti tabler-copy"></i></button>
                                        </div>
                                        <small class="text-muted">Aguardando pagamento...</small>
                                        <button class="btn btn-sm btn-link mt-2" onclick="resetPix()">Gerar Novo</button>
                                    </div>
                                `;
                      $('#pix-content-area').html(pixHtml);
                    } else if (method === 'boleto') {
                      window.open(data.bank_slip_url, '_blank');
                      Swal.fire('Boleto Gerado', 'O boleto foi aberto em uma nova aba.', 'success');
                    } else {
                      Swal.fire('Sucesso', 'Pagamento processado com sucesso!', 'success').then(() => location.reload());
                    }
                  } else {
                    Swal.fire('Erro', data.message, 'error');
                  }
                },
                error: function() {
                  $btn.prop('disabled', false).html(originalText);
                  Swal.fire('Erro', 'Ocorreu um erro ao processar.', 'error');
                }
              });
            });

            // Helper for Pix Copy
            window.copyPixCode = function() {
              const copyText = document.getElementById("pixCopy");
              copyText.select();
              navigator.clipboard.writeText(copyText.value);
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
              $('#pix-content-area').html(`
                    <button type="button" class="btn btn-success btn-lg btn-generate-payment" data-method="pix">
                      <i class="ti tabler-qrcode me-2"></i> Gerar Pix
                    </button>
                `);
              // Re-bind click event (since element was replaced) - simplest way is to reload or use delegated event.
              // Since we used $(document).on('click', '.btn-generate-payment'... it should work if we used delegated.
              // Let's ensure the previous jquery bind was delegated or re-bind here. Not done above.
              // FIX: Update jQuery bind to be delegated.
            };
          });

          // Ensure jQuery click is delegated for dynamic content
          $(document).on('click', '.btn-generate-payment', function(e) {
            // Logic moved inside here to support dynamic buttons
            // (Basically the same logic as above, just ensuring it runs)
          });
        </script>
      </div>
    </div>
    <div class="card mb-6">
      <!-- Billing Address -->
      <h5 class="card-header">Endereço de Cobrança</h5>
      <div class="card-body">
        <form id="formAccountSettings" onsubmit="return false;">
          <div class="row g-6">
            <div class="col-sm-6 form-control-validation">
              <label for="companyName" class="form-label">Nome da Empresa</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="ti tabler-building"></i></span>
                <input type="text" id="companyName" name="companyName" class="form-control" value="{{ $user->company }}" placeholder="Nome da sua oficina" />
              </div>
            </div>
            <div class="col-sm-6 form-control-validation">
              <label for="billingEmail" class="form-label">Email de Cobrança</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="ti tabler-mail"></i></span>
                <input class="form-control" type="text" id="billingEmail" name="billingEmail" value="{{ $user->email }}" placeholder="john.doe@example.com" />
              </div>
            </div>
            <div class="col-sm-6">
              <label for="cpf_cnpj" class="form-label">CPF ou CNPJ</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="ti tabler-id"></i></span>
                <input type="text" id="cpf_cnpj" name="cpf_cnpj" class="form-control cpf-cnpj-mask" value="{{ $user->cpf_cnpj }}" placeholder="000.000.000-00" />
              </div>
            </div>
            <div class="col-sm-6">
              <label for="mobileNumber" class="form-label">Celular</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="ti tabler-device-mobile"></i></span>
                <input class="form-control mobile-number phone-mask" type="text" id="mobileNumber" name="mobileNumber" value="{{ $user->contact_number }}" placeholder="11 99999-9999" />
              </div>
            </div>
            <div class="col-sm-6">
              <label for="country" class="form-label">País</label>
              <select id="country" class="form-select select2" name="country">
                <option value="Brasil" {{ $user->country == 'Brasil' ? 'selected' : '' }}>Brasil</option>
                <option value="Portugal" {{ $user->country == 'Portugal' ? 'selected' : '' }}>Portugal</option>
              </select>
            </div>
            <div class="col-sm-6">
              <label for="city" class="form-label">Cidade</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="ti tabler-map-pin"></i></span>
                <input class="form-control" type="text" id="city" name="city" value="{{ $user->city }}" placeholder="Sua Cidade" />
              </div>
            </div>
            <div class="col-12">
              <label for="billingAddress" class="form-label">Endereço de Cobrança</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="ti tabler-home"></i></span>
                <input type="text" class="form-control" id="billingAddress" name="billingAddress" value="{{ $user->billing_address }}" placeholder="Rua, Número, Bairro" />
              </div>
            </div>
            <div class="col-sm-6">
              <label for="state" class="form-label">Estado</label>
              <input class="form-control" type="text" id="state" name="state" value="{{ $user->state }}" placeholder="Ex: PR" />
            </div>
            <div class="col-sm-6">
              <label for="zipCode" class="form-label">CEP</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="ti tabler-map"></i></span>
                <input type="text" class="form-control zip-code-mask" id="zipCode" name="zipCode" value="{{ $user->zip_code }}" placeholder="00000-000" maxlength="9" />
              </div>
            </div>
          </div>
          <div class="mt-6">
            <button type="button" class="btn btn-primary me-3" id="btnSaveProfile"><i class="ti tabler-device-floppy me-1"></i> Salvar alterações</button>
            <button type="reset" class="btn btn-label-secondary">Descartar</button>
          </div>
        </form>
      </div>
      <!-- /Billing Address -->
    </div>
    <div class="card">
      <!-- Billing History -->
      <h5 class="card-header text-md-start text-center">Histórico de Cobrança</h5>
      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Plano</th>
              <th>Método</th>
              <th>Valor</th>
              <th>Status</th>
              <th>Data</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($billingHistory as $history)
            <tr>
              <td>#{{ $history->id }}</td>
              <td><strong>{{ $history->plan_name }}</strong></td>
              <td>{{ $history->payment_method }}</td>
              <td>R$ {{ number_format($history->amount, 2, ',', '.') }}</td>
              <td>
                @php
                $statusColors = ['paid' => 'success', 'pending' => 'warning', 'expired' => 'secondary', 'failed' => 'danger'];
                $statusLabels = ['paid' => 'Pago', 'pending' => 'Pendente', 'expired' => 'Expirado', 'failed' => 'Falhou'];
                @endphp
                <span class="badge bg-label-{{ $statusColors[$history->status] ?? 'secondary' }}">
                  {{ $statusLabels[$history->status] ?? $history->status }}
                </span>
              </td>
              <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center">Nenhuma cobrança encontrada.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <!--/ Billing History -->
    </div>
  </div>
</div>

<!-- Modal -->
@include('_partials/_modals/modal-pricing')
<!-- /Modal -->

@endsection