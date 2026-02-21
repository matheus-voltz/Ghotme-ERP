@extends('layouts/layoutMaster')

@section('title', 'Integrações')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
<div class="row">
    <!-- Payment Gateways Configuration -->
    <div class="col-12 mb-6">
        <div class="card">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">Configurações de Pagamento</h5>
                    <p class="text-muted small mb-0">Escolha seu gateway preferido e configure as credenciais.</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <label class="form-label mb-0 fw-bold text-primary">Gateway Ativo:</label>
                    <select name="active_payment_gateway" id="active_payment_gateway" form="formSettings" class="form-select w-px-200 border-primary">
                        <option value="asaas" {{ $settings->active_payment_gateway == 'asaas' ? 'selected' : '' }}>Asaas</option>
                        <option value="pagar_me" {{ $settings->active_payment_gateway == 'pagar_me' ? 'selected' : '' }}>Pagar.me</option>
                        <option value="pagbank" {{ $settings->active_payment_gateway == 'pagbank' ? 'selected' : '' }}>PagBank</option>
                        <option value="stripe" {{ $settings->active_payment_gateway == 'stripe' ? 'selected' : '' }}>Stripe</option>
                    </select>
                </div>
            </div>
            <div class="card-body pt-6">
                <form id="formSettings">
                    @csrf
                    <!-- Transferred active_payment_gateway reference here for simplicity -->
                    <input type="hidden" name="active_payment_gateway" id="hidden_gateway" value="{{ $settings->active_payment_gateway }}">

                    <div class="nav-align-top">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-asaas">
                                    <i class="ti tabler-brand-abstract me-1"></i> Asaas
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-pagarme">
                                    <i class="ti tabler-credit-card me-1"></i> Pagar.me
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-pagbank">
                                    <i class="ti tabler-building-bank me-1"></i> PagBank
                                </button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-stripe">
                                    <i class="ti tabler-brand-stripe me-1"></i> Stripe
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content pt-6 shadow-none border-0 px-0">
                            <!-- Asaas -->
                            <div class="tab-pane fade show active" id="tab-asaas" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-4">
                                            <label class="form-label">API Key do Asaas</label>
                                            <input type="password" name="asaas_api_key" class="form-control" value="{{ $settings->asaas_api_key }}" placeholder="Digite sua chave de API" />
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Ambiente Asaas</label>
                                            <select name="asaas_environment" class="form-select">
                                                <option value="sandbox" {{ $settings->asaas_environment == 'sandbox' ? 'selected' : '' }}>Homologação (Sandbox)</option>
                                                <option value="production" {{ $settings->asaas_environment == 'production' ? 'selected' : '' }}>Produção</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="alert alert-soft-primary d-flex align-items-center" role="alert">
                                            <span class="alert-icon text-primary me-2">
                                                <i class="ti tabler-info-circle ti-xs"></i>
                                            </span>
                                            <div>Asaas é ideal para Boletos e PIX com taxas competitivas.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Pagar.me -->
                            <div class="tab-pane fade" id="tab-pagarme" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-4">
                                            <label class="form-label">API Key (Pagar.me)</label>
                                            <input type="password" name="pagar_me_api_key" class="form-control" value="{{ $settings->pagar_me_api_key }}" placeholder="ak_test_..." />
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Encryption Key (Chave de Criptocrafia)</label>
                                            <input type="password" name="pagar_me_encryption_key" class="form-control" value="{{ $settings->pagar_me_encryption_key }}" placeholder="ek_test_..." />
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Ambiente Pagar.me</label>
                                            <select name="pagar_me_environment" class="form-select">
                                                <option value="sandbox" {{ $settings->pagar_me_environment == 'sandbox' ? 'selected' : '' }}>Homologação (Sandbox)</option>
                                                <option value="production" {{ $settings->pagar_me_environment == 'production' ? 'selected' : '' }}>Produção</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="alert alert-soft-info d-flex align-items-center" role="alert">
                                            <span class="alert-icon text-info me-2">
                                                <i class="ti tabler-info-circle ti-xs"></i>
                                            </span>
                                            <div>O Pagar.me oferece recursos avançados de checkout e antecipação.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- PagBank -->
                            <div class="tab-pane fade" id="tab-pagbank" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-4">
                                            <label class="form-label">Token de Acesso (PagBank)</label>
                                            <input type="password" name="pagbank_token" class="form-control" value="{{ $settings->pagbank_token }}" placeholder="Seu token PagBank" />
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Ambiente PagBank</label>
                                            <select name="pagbank_environment" class="form-select">
                                                <option value="sandbox" {{ $settings->pagbank_environment == 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                                                <option value="production" {{ $settings->pagbank_environment == 'production' ? 'selected' : '' }}>Produção</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="alert alert-soft-warning d-flex align-items-center" role="alert">
                                            <span class="alert-icon text-warning me-2">
                                                <i class="ti tabler-info-circle ti-xs"></i>
                                            </span>
                                            <div>PagBank (antigo PagSeguro) é amplamente conhecido e confiável.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Stripe -->
                            <div class="tab-pane fade" id="tab-stripe" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-4">
                                            <label class="form-label">Publicable Key (Stripe)</label>
                                            <input type="password" name="stripe_public_key" class="form-control" value="{{ $settings->stripe_public_key }}" placeholder="pk_test_..." />
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Secret Key (Stripe)</label>
                                            <input type="password" name="stripe_secret_key" class="form-control" value="{{ $settings->stripe_secret_key }}" placeholder="sk_test_..." />
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Webhook Secret (Stripe)</label>
                                            <input type="password" name="stripe_webhook_secret" class="form-control" value="{{ $settings->stripe_webhook_secret }}" placeholder="whsec_..." />
                                        </div>
                                        <div class="mb-4">
                                            <label class="form-label">Ambiente Stripe</label>
                                            <select name="stripe_environment" class="form-select">
                                                <option value="sandbox" {{ $settings->stripe_environment == 'sandbox' ? 'selected' : '' }}>Testes</option>
                                                <option value="production" {{ $settings->stripe_environment == 'production' ? 'selected' : '' }}>Produção/Live</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="alert alert-soft-dark d-flex align-items-center" role="alert">
                                            <span class="alert-icon text-dark me-2">
                                                <i class="ti tabler-brand-stripe ti-xs"></i>
                                            </span>
                                            <div>Stripe é o padrão global para pagamentos online.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-6">

                    <div class="row">
                        <!-- Fiscal -->
                        <div class="col-md-6 mb-4">
                            <div class="card bg-lighter shadow-none border">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-4">Integração Fiscal (Focus NFe)</h6>
                                    <div class="mb-3">
                                        <label class="form-label">API Token</label>
                                        <input type="password" name="fiscal_api_token" class="form-control bg-white" value="{{ $settings->fiscal_api_token }}" placeholder="Token Fiscal" />
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Ambiente Fiscal</label>
                                        <select name="fiscal_environment" class="form-select bg-white">
                                            <option value="sandbox" {{ $settings->fiscal_environment == 'sandbox' ? 'selected' : '' }}>Homologação</option>
                                            <option value="production" {{ $settings->fiscal_environment == 'production' ? 'selected' : '' }}>Produção</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Others -->
                        <div class="col-md-6 mb-4">
                            <div class="card bg-lighter shadow-none border">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-4">Comunicação (WhatsApp Meta)</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Access Token</label>
                                        <input type="password" name="whatsapp_token" class="form-control bg-white" value="{{ $settings->whatsapp_token }}" placeholder="Token Meta" />
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number ID</label>
                                        <input type="text" name="whatsapp_phone_number_id" class="form-control bg-white" value="{{ $settings->whatsapp_phone_number_id }}" placeholder="ID do Número" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <button type="submit" class="btn btn-primary px-5 btn-lg">
                            <i class="ti tabler-device-floppy me-2"></i> Salvar Todas as Integrações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formSettings');
        const gatewaySelect = document.getElementById('active_payment_gateway');
        const hiddenGateway = document.getElementById('hidden_gateway');

        // Sincroniza o select de fora com o hidden field do form
        gatewaySelect.addEventListener('change', function() {
            hiddenGateway.value = this.value;
        });

        form.onsubmit = function(e) {
            e.preventDefault();

            // Exibir loading
            Swal.fire({
                title: 'Salvando...',
                text: 'Por favor, aguarde enquanto atualizamos suas configurações.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new URLSearchParams(new FormData(form));

            fetch("{{ route('settings.integrations.update') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: data.message,
                            customClass: {
                                confirmButton: 'btn btn-success'
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: data.message || 'Houve um erro ao salvar.',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            }
                        });
                    }
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro de Conexão',
                        text: 'Não foi possível contatar o servidor.',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        }
                    });
                });
        };
    });
</script>
@endsection