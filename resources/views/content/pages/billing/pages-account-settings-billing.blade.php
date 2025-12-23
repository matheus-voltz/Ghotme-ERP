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
@vite(['resources/assets/js/pages-pricing.js', 'resources/assets/js/pages-account-settings-billing.js', 'resources/assets/js/app-invoice-list.js', 'resources/assets/js/modal-edit-cc.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="nav-align-top">
      <ul class="nav nav-pills flex-column flex-md-row mb-6">
        <li class="nav-item">
          <a class="nav-link" href="{{ url('pages/account-settings-account') }}"><i class="icon-base ti tabler-users icon-sm me-1_5"></i> Account</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('pages/account-settings-security') }}"><i class="icon-base ti tabler-lock icon-sm me-1_5"></i> Security</a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="javascript:void(0);"><i class="icon-base ti tabler-bookmark icon-sm me-1_5"></i> Billing & Plans</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('pages/account-settings-notifications') }}"><i class="icon-base ti tabler-bell icon-sm me-1_5"></i> Notifications</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ url('pages/account-settings-connections') }}"><i class="icon-base ti tabler-link icon-sm me-1_5"></i> Connections</a>
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
              <h6 class="mb-1">Seu plano atual é Básico</h6>
              <p>Um começo simples para todos</p>
            </div>
            <div class="mb-6">
              <h6 class="mb-1">Ativo até 09 de Dezembro de 2021</h6>
              <p>Enviaremos uma notificação quando a assinatura expirar</p>
            </div>
            <div>
              <h6 class="mb-1"><span class="me-1">$199 Per Month</span> <span class="badge bg-label-primary rounded-pill">Popular</span></h6>
              <p class="mb-1">Plano padrão para pequenas e médias empresas</p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="alert alert-warning mb-6" role="alert">
              <h5 class="alert-heading mb-1 d-flex align-items-center">
                <span class="alert-icon rounded"><i class="icon-base ti tabler-alert-triangle icon-md"></i></span>
                <span>Nós precisamos da sua atenção!</span>
              </h5>
              <span class="ms-11 ps-1">Seu plano precisa ser atualizado</span>
            </div>
            <div class="plan-statistics">
              <div class="d-flex justify-content-between">
                <h6 class="mb-1">Dias</h6>
                <h6 class="mb-1">12 de 30 Dias</h6>
              </div>
              <div class="progress rounded mb-1">
                <div class="progress-bar w-25 rounded" role="progressbar" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <small>18 dias restantes até que seu plano precise ser atualizado</small>
            </div>
          </div>
          <div class="col-12 d-flex gap-2 flex-wrap">
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#pricingModal">Fazer o upgrade</button>
            <button class="btn btn-label-danger cancel-subscription">Cancelar assinatura</button>
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
            <form id="creditCardForm" class="row g-6" onsubmit="return false">
              <div class="col-12 mb-2">
                <div class="form-check form-check-inline my-2 ms-2 me-6">
                  <input name="collapsible-payment" class="form-check-input" type="radio" value="" id="collapsible-payment-cc" checked="" />
                  <label class="form-check-label" for="collapsible-payment-cc">Cartão de Crédito/Débito/ATM</label>
                </div>
                <div class="form-check form-check-inline ms-2 my-2">
                  <input name="collapsible-payment" class="form-check-input" type="radio" value="" id="collapsible-payment-cash" />
                  <label class="form-check-label" for="collapsible-payment-cash">Conta Paypal</label>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label w-100" for="paymentCard">Número do Cartão</label>
                <div class="input-group input-group-merge">
                  <input id="paymentCard" name="paymentCard" class="form-control credit-card-mask" type="text" placeholder="1356 3215 6548 7898" aria-describedby="paymentCard2" />
                  <span class="input-group-text cursor-pointer" id="paymentCard2"><span class="card-type"></span></span>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label" for="paymentName">Nome</label>
                <input type="text" id="paymentName" class="form-control" placeholder="John Doe" />
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label" for="paymentExpiryDate">Data de Expiração</label>
                <input type="text" id="paymentExpiryDate" class="form-control expiry-date-mask" placeholder="MM/AA" />
              </div>
              <div class="col-6 col-md-3">
                <label class="form-label" for="paymentCvv">Código CVV</label>
                <div class="input-group input-group-merge">
                  <input type="text" id="paymentCvv" class="form-control cvv-code-mask" maxlength="3" placeholder="654" />
                  <span class="input-group-text cursor-pointer" id="paymentCvv2"><i class="icon-base ti tabler-help-circle text-body-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="Card Verification Value"></i></span>
                </div>
              </div>
              <div class="col-12">
                <div class="form-check form-switch ms-2 my-2">
                  <input type="checkbox" class="form-check-input" id="future-billing" />
                  <label for="future-billing" class="switch-label">Salvar cartão para faturamento futuro?</label>
                </div>
              </div>
              <div class="col-12 mt-6">
                <button type="submit" class="btn btn-primary me-3">Salvar Alterações</button>
                <button type="reset" class="btn btn-label-secondary">Cancelar</button>
              </div>
            </form>
          </div>
          <div class="col-md-6 mt-12 mt-md-0">
            <h6 class="mb-6">Meus Cartões</h6>
            <div class="added-cards">
              <div class="cardMaster p-6 bg-lighter rounded mb-6">
                <div class="d-flex justify-content-between flex-sm-row flex-column">
                  <div class="card-information me-2">
                    <img class="mb-2" src="{{ asset('assets/img/icons/payments/mastercard.png') }}" alt="Master Card" />
                    <div class="d-flex align-items-center mb-2 flex-wrap gap-2">
                      <h6 class="mb-0 me-2">Tom McBride</h6>
                      <span class="badge bg-label-primary">Primary</span>
                    </div>
                    <span class="card-number">&#8727;&#8727;&#8727;&#8727; &#8727;&#8727;&#8727;&#8727; 9856</span>
                  </div>
                  <div class="d-flex flex-column text-start text-lg-end">
                    <div class="d-flex order-sm-0 order-1 mt-sm-0 mt-4">
                      <button class="btn btn-sm btn-label-primary me-4" data-bs-toggle="modal" data-bs-target="#editCCModal">Edit</button>
                      <button class="btn btn-sm btn-label-danger">Delete</button>
                    </div>
                    <small class="mt-sm-4 mt-2 order-sm-1 order-0">Card expires at 12/26</small>
                  </div>
                </div>
              </div>
              <div class="cardMaster p-6 bg-lighter rounded">
                <div class="d-flex justify-content-between flex-sm-row flex-column">
                  <div class="card-information me-2">
                    <img class="mb-2" src="{{ asset('assets/img/icons/payments/visa.png') }}" alt="Visa Card" />
                    <h6 class="mb-2">Mildred Wagner</h6>
                    <span class="card-number">&#8727;&#8727;&#8727;&#8727; &#8727;&#8727;&#8727;&#8727; 5896</span>
                  </div>
                  <div class="d-flex flex-column text-start text-lg-end">
                    <div class="d-flex order-sm-0 order-1 mt-sm-0 mt-4">
                      <button class="btn btn-sm btn-label-primary me-4" data-bs-toggle="modal" data-bs-target="#editCCModal">Edit</button>
                      <button class="btn btn-sm btn-label-danger">Delete</button>
                    </div>
                    <small class="mt-sm-4 mt-2 order-sm-1 order-0">Card expires at 10/27</small>
                  </div>
                </div>
              </div>
            </div>
            <!-- Modal -->
            @include('_partials/_modals/modal-edit-cc')
            <!--/ Modal -->
          </div>
        </div>
      </div>
    </div>
    <div class="card mb-6">
      <!-- Billing Address -->
      <h5 class="card-header">Endereço de Cobrança</h5>
      <div class="card-body">
        <form id="formAccountSettings" onsubmit="return false">
          <div class="row g-6">
            <div class="col-sm-6 form-control-validation">
              <label for="companyName" class="form-label">Nome da Empresa</label>
              <input type="text" id="companyName" name="companyName" class="form-control" placeholder="{{ config('variables.creatorName') }}" />
            </div>
            <div class="col-sm-6 form-control-validation">
              <label for="billingEmail" class="form-label">Email de Cobrança</label>
              <input class="form-control" type="text" id="billingEmail" name="billingEmail" placeholder="john.doe@example.com" />
            </div>
            <div class="col-sm-6">
              <label for="taxId" class="form-label">ID Fiscal</label>
              <input type="text" id="taxId" name="taxId" class="form-control" placeholder="Enter Tax ID" />
            </div>
            <div class="col-sm-6">
              <label for="vatNumber" class="form-label">Número VAT</label>
              <input class="form-control" type="text" id="vatNumber" name="vatNumber" placeholder="Enter VAT Number" />
            </div>
            <div class="col-sm-6">
              <label for="mobileNumber" class="form-label">Celular</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text">BR (+55)</span>
                <input class="form-control mobile-number" type="text" id="mobileNumber" name="mobileNumber" placeholder="202 555 0111" />
              </div>
            </div>
            <div class="col-sm-6">
              <label for="country" class="form-label">País</label>
              <select id="country" class="form-select select2" name="country">
                <option selected>Brasil</option>
                <option>Canada</option>
                <option>UK</option>
                <option>Germany</option>
                <option>France</option>
              </select>
            </div>
            <div class="col-12">
              <label for="billingAddress" class="form-label">Endereço de Cobrança</label>
              <input type="text" class="form-control" id="billingAddress" name="billingAddress" placeholder="Billing Address" />
            </div>
            <div class="col-sm-6">
              <label for="state" class="form-label">Estado</label>
              <input class="form-control" type="text" id="state" name="state" placeholder="California" />
            </div>
            <div class="col-sm-6">
              <label for="zipCode" class="form-label">CEP</label>
              <input type="text" class="form-control zip-code" id="zipCode" name="zipCode" placeholder="231465" maxlength="6" />
            </div>
          </div>
          <div class="mt-6">
            <button type="submit" class="btn btn-primary me-3">Salvar alterações</button>
            <button type="reset" class="btn btn-label-secondary">Descartar</button>
          </div>
        </form>
      </div>
      <!-- /Billing Address -->
    </div>
    <div class="card">
      <!-- Billing History -->
      <h5 class="card-header text-md-start text-center">Histórico de Cobrança</h5>
      <div class="card-datatable border-top">
        <table class="invoice-list-table table border-top">
          <thead>
            <tr>
              <th></th>
              <th></th>
              <th>#</th>
              <th><i class="icon-base ti tabler-trending-up"></i></th>
              <th>Cliente</th>
              <th>Total</th>
              <th class="text-truncate">Issued Date</th>
              <th>Balance</th>
              <th>Invoice Status</th>
              <th class="cell-fit">Action</th>
            </tr>
          </thead>
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