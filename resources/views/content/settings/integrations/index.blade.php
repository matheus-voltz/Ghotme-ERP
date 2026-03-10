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
            <div class="card mb-6 shadow-sm">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center bg-label-primary py-3">
                    <div>
                        <h5 class="card-title mb-0 text-primary"><i class="ti tabler-credit-card me-1"></i> 1. Gateways de Pagamento</h5>
                        <p class="text-muted small mb-0">Configure como você deseja receber de seus clientes (Cartão, PIX, Boleto).</p>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <label class="form-label mb-0 fw-bold text-primary text-nowrap">Gateway Ativo:</label>
                        <select name="active_payment_gateway" class="form-select w-px-200 border-primary shadow-none">
                            <option value="asaas" {{ $settings->active_payment_gateway == 'asaas' ? 'selected' : '' }}>Asaas</option>
                            <option value="mercado_pago" {{ $settings->active_payment_gateway == 'mercado_pago' ? 'selected' : '' }}>Mercado Pago</option>
                            <option value="stripe" {{ $settings->active_payment_gateway == 'stripe' ? 'selected' : '' }}>Stripe (Internacional)</option>
                            <option value="pagar_me" {{ $settings->active_payment_gateway == 'pagar_me' ? 'selected' : '' }}>Pagar.me</option>
                            <option value="pagbank" {{ $settings->active_payment_gateway == 'pagbank' ? 'selected' : '' }}>PagBank</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-6">
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
                        </ul>
                        <div class="tab-content shadow-none border-start border-end border-bottom px-4 py-5 rounded-bottom">
                            <!-- Asaas -->
                            <div class="tab-pane fade show active" id="tab-asaas" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold">Chave de API (Produção ou Sandbox)</label>
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
                                            <p class="small mb-0">Melhor custo-benefício para PIX e Boletos automáticos com régua de cobrança personalizada.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Mercado Pago -->
                            <div class="tab-pane fade" id="tab-mp" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Public Key</label>
                                        <input type="text" name="mercado_pago_public_key" class="form-control" value="{{ $settings->mercado_pago_public_key }}" placeholder="APP_USR-..." />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Access Token</label>
                                        <input type="password" name="mercado_pago_access_token" class="form-control" value="{{ $settings->mercado_pago_access_token }}" placeholder="APP_USR-..." />
                                    </div>
                                    <div class="col-12 mt-2">
                                        <span class="badge bg-label-info">Info:</span> <small class="text-muted">Utilizado para Checkout Transparente e recebimentos instantâneos.</small>
                                    </div>
                                </div>
                            </div>
                            <!-- Pagar.me -->
                            <div class="tab-pane fade" id="tab-pagarme" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">API Key</label>
                                        <input type="password" name="pagar_me_api_key" class="form-control" value="{{ $settings->pagar_me_api_key }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Encryption Key</label>
                                        <input type="password" name="pagar_me_encryption_key" class="form-control" value="{{ $settings->pagar_me_encryption_key }}" />
                                    </div>
                                </div>
                            </div>
                            <!-- PagBank -->
                            <div class="tab-pane fade" id="tab-pagbank" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Token PagBank</label>
                                        <input type="password" name="pagbank_token" class="form-control" value="{{ $settings->pagbank_token }}" />
                                    </div>
                                </div>
                            </div>
                            <!-- Stripe -->
                            <div class="tab-pane fade" id="tab-stripe" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Secret Key</label>
                                        <input type="password" name="stripe_secret_key" class="form-control" value="{{ $settings->stripe_secret_key }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Webhook Secret</label>
                                        <input type="password" name="stripe_webhook_secret" class="form-control" value="{{ $settings->stripe_webhook_secret }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WHATSAPP -->
            <div class="card mb-6 shadow-sm">
                <div class="card-header border-bottom bg-label-success py-3">
                    <h5 class="card-title mb-0 text-success"><i class="ti tabler-brand-whatsapp me-1"></i> 2. Comunicação via WhatsApp</h5>
                    <p class="text-muted small mb-0">Envie notificações automáticas de {{ niche('order_label') ?? 'Ordens de Serviço' }} para seus clientes.</p>
                </div>
                <div class="card-body p-6">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <div class="p-3 rounded bg-lighter border-dashed">
                                <h6 class="fw-bold mb-3"><i class="ti tabler-api me-1"></i> API Própria (Evolution / Z-API)</h6>
                                <div class="mb-3">
                                    <label class="form-label">URL da API</label>
                                    <input type="text" name="whatsapp_api_url" class="form-control bg-white" value="{{ $settings->whatsapp_api_url }}" placeholder="https://api.seuserver.com" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Instância</label>
                                    <input type="text" name="whatsapp_instance_id" class="form-control bg-white" value="{{ $settings->whatsapp_instance_id }}" placeholder="ex: GhotmeEmpresa" />
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">API Key / Token</label>
                                    <input type="password" name="whatsapp_api_key" class="form-control bg-white" value="{{ $settings->whatsapp_api_key }}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3">
                                <h6 class="fw-bold mb-3 text-muted"><i class="ti tabler-brand-meta me-1"></i> Business Cloud (API Oficial Meta)</h6>
                                <div class="mb-3">
                                    <label class="form-label">Token de Acesso</label>
                                    <input type="password" name="whatsapp_token" class="form-control" value="{{ $settings->whatsapp_token }}" />
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Phone Number ID</label>
                                    <input type="text" name="whatsapp_phone_number_id" class="form-control" value="{{ $settings->whatsapp_phone_number_id }}" />
                                </div>
                                <small class="text-info d-block mt-3"><i class="ti tabler-info-circle me-1"></i> Recomendado para grandes volumes e verificação oficial.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if(get_current_niche() !== "food_service")
            <!-- MARKETPLACE -->
            <div class="card mb-6 border-warning shadow-none">
                <div class="card-header border-bottom bg-label-warning py-3">
                    <h5 class="card-title mb-0 text-warning"><i class="ti tabler-building-store me-1"></i> 3. Marketplace (Mercado Livre)</h5>
                    <p class="text-muted small mb-0">Publique seus {{ niche('inventory_items') ?? 'Produtos' }} automaticamente no Mercado Livre.</p>
                </div>
                <div class="card-body p-6">
                    <div class="row g-4 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Client ID</label>
                            <input type="text" name="meli_client_id" class="form-control" value="{{ $settings->meli_client_id }}" placeholder="Ex: 123456789" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Client Secret</label>
                            <input type="password" name="meli_client_secret" class="form-control" value="{{ $settings->meli_client_secret }}" placeholder="Ex: abc...xyz" />
                        </div>
                        <div class="col-md-4">
                            @if($settings->meli_active && $settings->meli_access_token)
                            <div class="alert alert-success d-flex align-items-center mb-0 p-2 border-dashed">
                                <i class="ti tabler-circle-check-filled me-2 ti-md"></i>
                                <div>
                                    <span class="fw-bold d-block small">Conta Conectada</span>
                                    <a href="{{ route('meli.redirect') }}" class="btn btn-sm btn-success mt-1 py-0 px-2">Reconectar</a>
                                </div>
                            </div>
                            @else
                            <a href="{{ route('meli.redirect') }}" class="btn btn-warning w-100 shadow-warning fw-bold">
                                <i class="ti tabler-link me-1"></i> Conectar Mercado Livre
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>


            @endif


            @if(get_current_niche() === "food_service")
            <!-- IFOOD -->
            <div class="card mb-6 border-danger shadow-none">
                <div class="card-header border-bottom bg-label-danger py-3">
                    <h5 class="card-title mb-0 text-danger"><i class="ti tabler-tools-kitchen-2 me-1"></i> 3. Delivery iFood (Gestão de Pedidos)</h5>
                    <p class="text-muted small mb-0">Receba e gerencie seus pedidos do iFood diretamente no painel Ghotme.</p>
                </div>
                <div class="card-body p-6">
                    <div class="row g-4 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Merchant ID (Loja iFood)</label>
                            <input type="text" name="ifood_merchant_id" class="form-control border-danger" value="{{ auth()->user()?->company?->ifood_merchant_id }}" placeholder="Ex: 5763132e-..." />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Client ID</label>
                            <input type="text" name="ifood_client_id" class="form-control" value="{{ auth()->user()?->company?->ifood_client_id }}" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Client Secret</label>
                            <input type="password" name="ifood_client_secret" class="form-control" value="{{ auth()->user()?->company?->ifood_client_secret }}" />
                        </div>
                        <div class="col-12 mt-3">
                            <div class="alert alert-dark d-flex align-items-center mb-0 border-dashed">
                                <i class="ti tabler-info-circle me-2"></i>
                                <div>
                                    URL de Webhook iFood: <code class="fw-bold text-warning">{{ url('/api/webhooks/ifood') }}</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- FISCAL -->
            <div class="card mb-6 shadow-sm border-start border-info border-3">
                <div class="card-header border-bottom py-3">
                    <h5 class="card-title mb-0 text-info"><i class="ti tabler-file-invoice me-1"></i> 4. Emissão Fiscal (NFe / NFSe)</h5>
                    <p class="text-muted small mb-0">Emita notas fiscais de {{ get_current_niche() === 'food_service' ? 'vendas' : 'serviços e peças' }} automaticamente.</p>
                </div>
                <div class="card-body p-6">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Token de API (Focus NFe)</label>
                            <input type="password" name="fiscal_api_token" class="form-control" value="{{ $settings->fiscal_api_token }}" placeholder="Token fornecido pela Focus" />
                            <div class="mt-3">
                                <label class="form-label fw-bold">Ambiente Fiscal</label>
                                <select name="fiscal_environment" class="form-select w-px-200">
                                    <option value="sandbox" {{ $settings->fiscal_environment == 'sandbox' ? 'selected' : '' }}>Homologação (Testes)</option>
                                    <option value="production" {{ $settings->fiscal_environment == 'production' ? 'selected' : '' }}>Produção (Real)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <img src="https://focusnfe.com.br/wp-content/themes/focusnfe/assets/img/logo.png" alt="Focus NFe" style="max-width: 150px; opacity: 0.8; filter: grayscale(1);">
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTÃO SALVAR -->
            <div class="card bg-primary shadow-primary">
                <div class="card-body d-flex justify-content-between align-items-center py-3">
                    <span class="text-white small"><i class="ti tabler-shield-lock me-1"></i> Suas chaves de API estão protegidas por criptografia AES-256.</span>
                    <button type="submit" class="btn btn-white btn-lg px-5 fw-bold text-primary">
                        <i class="ti tabler-device-floppy me-2"></i> Salvar Alterações
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
                title: 'Salvando Configurações',
                text: 'Aguarde um momento...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
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
                            text: data.message,
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            }
                        });
                    }
                })
                .catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro crítico',
                        text: 'Não foi possível salvar as configurações.'
                    });
                });
        };
    });
</script>

<style>
    .shadow-primary {
        box-shadow: 0 0.5rem 1.5rem rgba(115, 103, 240, 0.4) !important;
    }

    .bg-lighter {
        background-color: #f8f7fa !important;
    }

    .nav-tabs .nav-link.active {
        border-bottom: 2px solid #7367f0 !important;
        color: #7367f0 !important;
        font-weight: bold;
    }

    .border-dashed {
        border-style: dashed !important;
    }

    .btn-white {
        background: #fff;
        border-color: #fff;
    }

    .btn-white:hover {
        background: #f2f2f2;
    }
</style>
@endsection