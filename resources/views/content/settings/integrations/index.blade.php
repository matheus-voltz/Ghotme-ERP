@extends('layouts/layoutMaster')

@section('title', 'Integrações e Gateways')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Configurações /</span> Integrações
</h4>

<div class="row">
    <div class="col-12">
        <form id="formSettings">
            @csrf
            
            <!-- PAGAMENTOS -->
            <div class="card mb-6">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">1. Gateways de Pagamento</h5>
                        <p class="text-muted small mb-0">Configure como você deseja receber de seus clientes (Cartão, PIX, Boleto).</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <label class="form-label mb-0 fw-bold text-primary text-nowrap">Gateway Ativo:</label>
                        <select name="active_payment_gateway" class="form-select w-px-200 border-primary">
                            <option value="asaas" {{ $settings->active_payment_gateway == 'asaas' ? 'selected' : '' }}>Asaas</option>
                            <option value="mercado_pago" {{ $settings->active_payment_gateway == 'mercado_pago' ? 'selected' : '' }}>Mercado Pago</option>
                            <option value="stripe" {{ $settings->active_payment_gateway == 'stripe' ? 'selected' : '' }}>Stripe (Internacional)</option>
                            <option value="pagar_me" {{ $settings->active_payment_gateway == 'pagar_me' ? 'selected' : '' }}>Pagar.me</option>
                            <option value="pagbank" {{ $settings->active_payment_gateway == 'pagbank' ? 'selected' : '' }}>PagBank</option>
                        </select>
                    </div>
                </div>
                <div class="card-body pt-4">
                    <div class="nav-align-top mb-4">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-asaas"><i class="ti tabler-brand-abstract me-1"></i> Asaas</button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-mp"><i class="ti tabler-brand-shopee me-1"></i> Mercado Pago</button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-pagarme"><i class="ti tabler-credit-card me-1"></i> Pagar.me</button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-pagbank"><i class="ti tabler-building-bank me-1"></i> PagBank</button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-stripe"><i class="ti tabler-brand-stripe me-1"></i> Stripe</button>
                            </li>
                            <li class="nav-item">
                                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-bitcoin"><i class="ti tabler-currency-bitcoin me-1"></i> Bitcoin</button>
                            </li>
                        </ul>
                        <div class="tab-content shadow-none border px-4 py-5">
                            <!-- Asaas -->
                            <div class="tab-pane fade show active" id="tab-asaas" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Chave de API (Produção ou Sandbox)</label>
                                        <input type="password" name="asaas_api_key" class="form-control" value="{{ $settings->asaas_api_key }}" placeholder="$a_..." />
                                        <div class="mt-3">
                                            <label class="form-label">Ambiente</label>
                                            <select name="asaas_environment" class="form-select">
                                                <option value="sandbox" {{ $settings->asaas_environment == 'sandbox' ? 'selected' : '' }}>Homologação (Testes)</option>
                                                <option value="production" {{ $settings->asaas_environment == 'production' ? 'selected' : '' }}>Produção (Real)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="alert alert-outline-primary">
                                            <h6 class="alert-heading fw-bold mb-1">Por que Asaas?</h6>
                                            <p class="small mb-0">Melhor custo-benefício para PIX e Boletos automáticos com régua de cobrança.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Mercado Pago -->
                            <div class="tab-pane fade" id="tab-mp" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Public Key</label>
                                        <input type="text" name="mercado_pago_public_key" class="form-control" value="{{ $settings->mercado_pago_public_key }}" placeholder="APP_USR-..." />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Access Token</label>
                                        <input type="password" name="mercado_pago_access_token" class="form-control" value="{{ $settings->mercado_pago_access_token }}" placeholder="APP_USR-..." />
                                    </div>
                                    <div class="col-12 mt-2">
                                        <span class="badge bg-label-info">Info:</span> <small class="text-muted">Utilizado para Checkout Transparente e recebimentos rápidos.</small>
                                    </div>
                                </div>
                            </div>
                            <!-- Pagar.me -->
                            <div class="tab-pane fade" id="tab-pagarme" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">API Key</label>
                                        <input type="password" name="pagar_me_api_key" class="form-control" value="{{ $settings->pagar_me_api_key }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Encryption Key</label>
                                        <input type="password" name="pagar_me_encryption_key" class="form-control" value="{{ $settings->pagar_me_encryption_key }}" />
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Ambiente</label>
                                        <select name="pagar_me_environment" class="form-select">
                                            <option value="sandbox" {{ $settings->pagar_me_environment == 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                                            <option value="production" {{ $settings->pagar_me_environment == 'production' ? 'selected' : '' }}>Produção</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- PagBank -->
                            <div class="tab-pane fade" id="tab-pagbank" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label">Token PagBank</label>
                                        <input type="password" name="pagbank_token" class="form-control" value="{{ $settings->pagbank_token }}" />
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Ambiente</label>
                                        <select name="pagbank_environment" class="form-select">
                                            <option value="sandbox" {{ $settings->pagbank_environment == 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                                            <option value="production" {{ $settings->pagbank_environment == 'production' ? 'selected' : '' }}>Produção</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Stripe -->
                            <div class="tab-pane fade" id="tab-stripe" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Secret Key</label>
                                        <input type="password" name="stripe_secret_key" class="form-control" value="{{ $settings->stripe_secret_key }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Webhook Secret</label>
                                        <input type="password" name="stripe_webhook_secret" class="form-control" value="{{ $settings->stripe_webhook_secret }}" />
                                    </div>
                                </div>
                            </div>
                            <!-- Bitcoin -->
                            <div class="tab-pane fade" id="tab-bitcoin" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">API Key (BTCPay/Gateway)</label>
                                        <input type="password" name="bitcoin_api_key" class="form-control" value="{{ $settings->bitcoin_api_key }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Webhook Secret</label>
                                        <input type="password" name="bitcoin_webhook_secret" class="form-control" value="{{ $settings->bitcoin_webhook_secret }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WHATSAPP -->
            <div class="card mb-6">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">2. Comunicação via WhatsApp</h5>
                    <p class="text-muted small mb-0">Escolha como o sistema deve enviar mensagens para seus clientes.</p>
                </div>
                <div class="card-body pt-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-lighter">
                                <h6 class="fw-bold mb-3"><i class="ti tabler-api me-1"></i> API Própria (Evolution / Z-API / Gateway)</h6>
                                <div class="mb-3">
                                    <label class="form-label">URL da API</label>
                                    <input type="text" name="whatsapp_api_url" class="form-control bg-white" value="{{ $settings->whatsapp_api_url }}" placeholder="https://api.seuserver.com" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nome da Instância</label>
                                    <input type="text" name="whatsapp_instance_id" class="form-control bg-white" value="{{ $settings->whatsapp_instance_id }}" placeholder="ex: GhotmeEmpresa" />
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">API Key / Token</label>
                                    <input type="password" name="whatsapp_api_key" class="form-control bg-white" value="{{ $settings->whatsapp_api_key }}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded">
                                <h6 class="fw-bold mb-3 text-muted"><i class="ti tabler-brand-meta me-1"></i> WhatsApp Business Cloud (Meta)</h6>
                                <div class="mb-3">
                                    <label class="form-label">Token de Acesso</label>
                                    <input type="password" name="whatsapp_token" class="form-control" value="{{ $settings->whatsapp_token }}" />
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Phone Number ID</label>
                                    <input type="text" name="whatsapp_phone_number_id" class="form-control" value="{{ $settings->whatsapp_phone_number_id }}" />
                                </div>
                                <small class="text-info d-block mt-2">Recomendado para grandes volumes e mensagens oficiais.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FISCAL -->
            <div class="card mb-6">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">3. Emissão de Notas Fiscais (NFe / NFSe)</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <label class="form-label">Token de API (Focus NFe)</label>
                            <input type="password" name="fiscal_api_token" class="form-control" value="{{ $settings->fiscal_api_token }}" placeholder="Token fornecido pela Focus" />
                            <div class="mt-3">
                                <label class="form-label">Ambiente Fiscal</label>
                                <select name="fiscal_environment" class="form-select w-px-200">
                                    <option value="sandbox" {{ $settings->fiscal_environment == 'sandbox' ? 'selected' : '' }}>Homologação</option>
                                    <option value="production" {{ $settings->fiscal_environment == 'production' ? 'selected' : '' }}>Produção</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <img src="https://focusnfe.com.br/wp-content/themes/focusnfe/assets/img/logo.png" alt="Focus NFe" style="max-width: 150px; opacity: 0.7;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <span class="text-muted small"><i class="ti tabler-shield-check me-1"></i> Suas credenciais são armazenadas com criptografia de ponta.</span>
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow-primary">
                        <i class="ti tabler-device-floppy me-2"></i> Salvar Todas as Configurações
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formSettings');

    form.onsubmit = function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Salvando Integrações',
            text: 'Aguarde um momento...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const formData = new FormData(form);

        fetch("{{ route('settings.integrations.update') }}", {
            method: 'POST',
            body: new URLSearchParams(formData),
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Sucesso!', text: data.message, customClass: { confirmButton: 'btn btn-success' } });
            } else {
                Swal.fire({ icon: 'error', title: 'Erro!', text: data.message, customClass: { confirmButton: 'btn btn-danger' } });
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Erro crítico', text: 'Não foi possível salvar as configurações.' });
        });
    };
});
</script>

<style>
    .shadow-primary { box-shadow: 0 0.5rem 1rem rgba(115, 103, 240, 0.3) !important; }
    .bg-lighter { background-color: #f8f7fa !important; }
    .nav-tabs .nav-link.active { border-bottom-color: #7367f0 !important; color: #7367f0 !important; }
</style>
@endsection
