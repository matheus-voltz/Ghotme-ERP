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
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(data) {
                $btnSave.html(originalText).prop('disabled', false);
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Sucesso!', text: data.message });
                } else {
                    Swal.fire({ icon: 'error', title: 'Erro!', text: data.message });
                }
            },
            error: function() {
                $btnSave.html(originalText).prop('disabled', false);
                Swal.fire({ icon: 'error', title: 'Erro!', text: 'Erro ao salvar perfil.' });
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
        
        // Force plan selection if user is on 'free' plan
        const currentPlan = "{{ $user->plan }}";
        if (currentPlan === 'free') {
            pendingMethod = method;
            Swal.fire({
                icon: 'info',
                title: 'Selecione um Plano',
                text: 'Para gerar um pagamento, você precisa primeiro escolher qual plano deseja assinar.',
                confirmButtonText: 'Ver Planos',
                customClass: { confirmButton: 'btn btn-primary' },
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
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            }).then(() => {
                $('input[name="cpf_cnpj"]').focus();
                $('input[name="cpf_cnpj"]')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
            return;
        }
        
        $btn.html('<span class="spinner-border spinner-border-sm" role="status"></span>').prop('disabled', true);

        $.ajax({
            url: "{{ route('settings.generate-payment') }}",
            method: 'POST',
            data: JSON.stringify({ method: method }),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
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
                    Swal.fire({ icon: 'success', title: 'Cobrança Gerada!', timer: 2000, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Erro!', text: data.message });
                }
            },
            error: function() {
                $btn.html(originalText).prop('disabled', false);
                Swal.fire({ icon: 'error', title: 'Erro!', text: 'Erro ao gerar pagamento.' });
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
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            }).then(() => {
                $('input[name="cpf_cnpj"]').focus();
                $('input[name="cpf_cnpj"]')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
            return;
        }

        $btn.html('<span class="spinner-border spinner-border-sm" role="status"></span>').prop('disabled', true);

        $.ajax({
            url: "{{ route('settings.select-plan') }}",
            method: 'POST',
            data: JSON.stringify({ plan: plan, type: type, method: pendingMethod }),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(data) {
                if (data.success) {
                    if (data.redirect_url || data.invoice_url) {
                        window.open(data.redirect_url || data.invoice_url, '_blank');
                    }
                    location.reload();
                } else {
                    $btn.html(originalText).prop('disabled', false);
                    Swal.fire({ icon: 'error', title: 'Erro', text: data.message });
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
    Swal.fire({ icon: 'success', title: 'Copiado!', timer: 1000, showConfirmButton: false });
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
              @if($user->plan === 'free' && $user->cpf_cnpj)
                <p class="text-primary fw-bold mt-2 animate__animated animate__pulse animate__infinite">
                  <i class="icon-base ti tabler-arrow-narrow-right"></i> Selecione um plano para continuar
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
                  <h6 class="mb-0">{{ number_format($daysUsed, 12, '.', '') }} de 30 Dias</h6>
                </div>
                <div class="progress mb-1" style="height: 8px;">
                  @php $percent = min(100, max(0, ($daysUsed / 30) * 100)); @endphp
                  <div class="progress-bar {{ $trialExpired ? 'bg-danger' : '' }}" role="progressbar" style="width: {{ $percent }}%"></div>
                </div>
                <small>{{ number_format(max(0, $trialDaysLeft), 12, '.', '') }} dias restantes de teste grátis</small>
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
      <h5 class="card-header">Métodos de Pagamento</h5>
      <div class="card-body">
        <div class="row gx-6">
          <div class="col-md-6">
            <div class="payment-methods-list">
              @if($user->plan_type === 'monthly')
                <!-- Pix (Somente Mensal) -->
                <div class="d-flex align-items-center mb-4 p-3 border rounded justify-content-between">
                  <div class="d-flex align-items-center">
                    <div class="avatar bg-label-success me-3 p-2"><i class="icon-base ti tabler-qrcode fs-3"></i></div>
                    <div><h6 class="mb-0">Pix</h6><small class="text-muted">Aprovação imediata</small></div>
                  </div>
                  <button class="btn btn-xs btn-label-success btn-generate-payment" data-method="pix">Selecionar</button>
                </div>
                <!-- Boleto Mensal -->
                <div class="d-flex align-items-center mb-4 p-3 border rounded justify-content-between">
                  <div class="d-flex align-items-center">
                    <div class="avatar bg-label-info me-3 p-2"><i class="icon-base ti tabler-barcode fs-3"></i></div>
                    <div><h6 class="mb-0">Boleto Mensalidade</h6><small class="text-muted">Vencimento em 3 dias</small></div>
                  </div>
                  <button class="btn btn-xs btn-label-info btn-generate-payment" data-method="boleto">Selecionar</button>
                </div>
                <!-- Cartão Recorrente -->
                <div class="d-flex align-items-center p-3 border rounded justify-content-between">
                  <div class="d-flex align-items-center">
                    <div class="avatar bg-label-primary me-3 p-2"><i class="icon-base ti tabler-credit-card fs-3"></i></div>
                    <div><h6 class="mb-0">Cartão Recorrente</h6><small class="text-muted">Assinatura mensal</small></div>
                  </div>
                  <button class="btn btn-xs btn-label-primary btn-generate-payment" data-method="credit_card">Selecionar</button>
                </div>
              @else
                <!-- Boleto Único (Anual) -->
                <div class="d-flex align-items-center mb-4 p-3 border rounded justify-content-between">
                  <div class="d-flex align-items-center">
                    <div class="avatar bg-label-info me-3 p-2"><i class="icon-base ti tabler-barcode fs-3"></i></div>
                    <div><h6 class="mb-0">Boleto Único Anual</h6><small class="text-muted">Pagamento à vista</small></div>
                  </div>
                  <button class="btn btn-xs btn-label-info btn-generate-payment" data-method="boleto">Selecionar</button>
                </div>
                <!-- Cartão Parcelado (Anual) -->
                <div class="d-flex align-items-center p-3 border rounded justify-content-between">
                  <div class="d-flex align-items-center">
                    <div class="avatar bg-label-primary me-3 p-2"><i class="icon-base ti tabler-credit-card fs-3"></i></div>
                    <div><h6 class="mb-0">Cartão Parcelado</h6><small class="text-muted">Parcele em até 12x</small></div>
                  </div>
                  <button class="btn btn-xs btn-label-primary btn-generate-payment" data-method="credit_card">Selecionar</button>
                </div>
              @endif
            </div>
          </div>
          <div class="col-md-6 mt-6 mt-md-0">
            <div id="payment-result-container" class="d-none">
                <div class="card bg-label-secondary border-0 shadow-none">
                    <div class="card-body text-center" id="payment-result-content">
                        <!-- Conteúdo dinâmico via JS -->
                    </div>
                </div>
            </div>
            <div id="payment-info-default">
                <h6 class="mb-6">Pagamento Seguro</h6>
                <p>Utilizamos o <strong>Asaas</strong> para processar seus pagamentos com segurança. Suas informações de cartão são criptografadas e nunca ficam salvas em nossos servidores.</p>
                <div class="d-flex align-items-center gap-3">
                  <img src="https://static.asaas.com/img/brand/asaas-logo.png" alt="Asaas" height="30">
                  <span class="badge bg-label-primary">Ambiente Seguro</span>
                </div>
            </div>
          </div>
        </div>
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