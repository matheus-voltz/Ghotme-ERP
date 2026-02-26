@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/commonMaster')

@section('title', 'Aprovação de Orçamento')

@section('page-style')
<!-- Google Fonts: Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --portal-primary: #7367f0;
        --portal-bg: #f8f7fa;
        --portal-success: #28c76f;
    }

    [data-bs-theme="dark"] {
        --portal-bg: #2f3349;
    }

    body {
        background-color: var(--portal-bg) !important;
        font-family: 'Inter', sans-serif !important;
    }

    .budget-header {
        background: linear-gradient(135deg, #7367f0 0%, #4831d4 100%);
        padding: 4rem 0 6rem;
        color: white;
        text-align: center;
        border-radius: 0 0 3rem 3rem;
        box-shadow: 0 10px 40px rgba(115, 103, 240, 0.15);
        position: relative;
        z-index: 1;
    }

    .detail-container {
        margin-top: -4rem;
        position: relative;
        z-index: 2;
    }

    .detail-card {
        border: none;
        border-radius: 2rem;
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.05);
        background: var(--bs-card-bg, white);
        overflow: hidden;
    }

    .table thead th {
        background: rgba(0, 0, 0, 0.02);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        color: #888;
        border-bottom: 2px solid rgba(0, 0, 0, 0.05);
    }

    .payment-option-card {
        border: 2px solid rgba(0, 0, 0, 0.05);
        border-radius: 1.25rem;
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .payment-option-card:hover {
        border-color: var(--portal-primary);
        background: #fdfdff;
    }

    .payment-option-input:checked+.payment-option-card {
        border-color: var(--portal-primary);
        background: rgba(115, 103, 240, 0.05);
    }

    .payment-option-input:checked+.payment-option-card::after {
        content: '✓';
        position: absolute;
        top: 10px;
        right: 15px;
        color: var(--portal-primary);
        font-weight: bold;
    }

    .whatsapp-fab {
        position: fixed;
        bottom: 2rem;
        left: 2rem;
        width: 60px;
        height: 60px;
        background: #25d366;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        box-shadow: 0 10px 30px rgba(37, 211, 102, 0.3);
        z-index: 1000;
    }

    .animate-up {
        animation: fadeInUp 0.5s both;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @include('content.public.customer-portal.chat-widget-css')
</style>
@endsection

@section('layoutContent')

<!-- Header -->
<div class="budget-header w-100 animate-up" style="animation-delay: 0.1s">
    <div class="container">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <a href="{{ route('customer.portal.index', $budget->client->uuid) }}" class="btn bg-white text-primary rounded-pill shadow-sm border-0">
                <i class="ti tabler-arrow-left me-2"></i> Voltar ao Portal
            </a>
            <span class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm">Orçamento Digital</span>
        </div>

        @if($budget->company->logo_path)
        <img src="{{ asset('storage/' . $budget->company->logo_path) }}" alt="Logo" class="mb-4 bg-white p-2 rounded-3 shadow-sm" style="max-height: 70px;">
        @endif
        <h1 class="text-white fw-bold mb-2">Orçamento #{{ $budget->id }}</h1>
        <p class="text-white opacity-75 fs-5">{{ $budget->company->name }}</p>
    </div>
</div>

<div class="container detail-container pb-5">
    <!-- Conteúdo do Orçamento -->
    <div class="detail-card animate-up" style="animation-delay: 0.2s">
        <div class="card-body p-4 p-lg-5">
            <div class="row mb-5 gy-4">
                <div class="col-md-6 border-end">
                    <h6 class="text-muted text-uppercase mb-3">Informações do Cliente</h6>
                    <h5 class="fw-bold mb-1">{{ $budget->client->name }}</h5>
                    <p class="mb-0 text-muted">{{ $budget->veiculo->marca }} {{ $budget->veiculo->modelo }} • {{ $budget->veiculo->placa }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h6 class="text-muted text-uppercase mb-3">Validade do Orçamento</h6>
                    <h5 class="fw-bold mb-1">{{ $budget->valid_until ? $budget->valid_until->format('d/m/Y') : 'Data não definida' }}</h5>
                    <p class="mb-0 text-muted">Aprovado: @if($budget->status == 'approved') Sim @else Não @endif</p>
                </div>
            </div>

            <h5 class="fw-bold mb-4">Detalhamento de Serviços e Peças</h5>
            <div class="table-responsive rounded-3 border">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="py-3 px-4">Descrição</th>
                            <th class="text-center py-3">Tipo</th>
                            <th class="text-center py-3">Qtd</th>
                            <th class="text-end py-3 px-4">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($budget->items as $item)
                        <tr>
                            <td class="px-4 py-3 fw-medium">{{ $item->service->name ?? $item->description }}</td>
                            <td class="text-center"><span class="badge bg-label-primary">Serviço</span></td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end px-4">R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        @foreach($budget->parts as $part)
                        <tr>
                            <td class="px-4 py-3 fw-medium">{{ $part->part->name ?? $part->description }}</td>
                            <td class="text-center"><span class="badge bg-label-info">Peça</span></td>
                            <td class="text-center">{{ $part->quantity }}</td>
                            <td class="text-end px-4">R$ {{ number_format($part->price * $part->quantity, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th colspan="3" class="text-end py-4 px-4 h5 mb-0 fw-bold">Total a Investir:</th>
                            <th class="text-end py-4 px-4 h4 mb-0 fw-bold text-primary">R$ {{ number_format($budget->total, 2, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($budget->description)
            <div class="mt-5 p-4 rounded-4 bg-label-secondary border-dashed">
                <h6 class="fw-bold"><i class="ti tabler-info-circle me-2"></i>Notas do Consultor:</h6>
                <p class="mb-0 lh-lg">{{ $budget->description }}</p>
            </div>
            @endif

            <!-- Área de Ação -->
            <div class="mt-5 pt-4 border-top">
                @if($budget->status == 'pending')
                <form action="{{ route('public.budget.approve', $budget->uuid) }}" method="POST" id="approvalForm" onsubmit="return handleApproval(event)">
                    @csrf
                    @if(($paymentMethodsCount ?? 0) > 0)
                    <div class="row g-4 mb-5">
                        <div class="col-12 text-center">
                            <h5 class="fw-bold mb-4">Deseja realizar o pagamento antecipado?</h5>
                        </div>
                        <div class="col-md-6">
                            <input type="radio" name="early_payment" value="1" id="early_yes" class="payment-option-input d-none" checked onchange="updateButtonText()">
                            <label for="early_yes" class="payment-option-card w-100 h-100">
                                <h6 class="fw-bold mb-2">Sim, antecipado</h6>
                                <p class="text-muted small mb-0">Agilize a liberação das peças e a entrega do seu veículo.</p>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <input type="radio" name="early_payment" value="0" id="early_no" class="payment-option-input d-none" onchange="updateButtonText()">
                            <label for="early_no" class="payment-option-card w-100 h-100">
                                <h6 class="fw-bold mb-2">Não, pagar na retirada</h6>
                                <p class="text-muted small mb-0">O pagamento será realizado integralmente no momento da entrega.</p>
                            </label>
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="early_payment" value="0" id="early_no">
                    <input type="checkbox" id="early_yes" class="d-none" disabled> {{-- Placeholder for JS --}}
                    @endif

                    <div class="d-flex flex-column flex-md-row gap-3 justify-content-center mt-5">
                        <button type="button" class="btn btn-outline-danger btn-lg px-5 rounded-pill" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            Rejeitar Orçamento
                        </button>
                        <button type="submit" id="btnApprove" class="btn btn-primary btn-lg px-5 rounded-pill shadow-lg">
                            Pagar Agora e Aprovar <i class="ti tabler-credit-card ms-2"></i>
                        </button>
                    </div>
                </form>

                <script>
                    function updateButtonText() {
                        const isEarly = document.getElementById('early_yes').checked;
                        const btn = document.getElementById('btnApprove');
                        if (isEarly) {
                            btn.innerHTML = 'Pagar Agora e Aprovar <i class="ti tabler-credit-card ms-2"></i>';
                            btn.classList.add('btn-success');
                            btn.classList.remove('btn-primary');
                        } else {
                            btn.innerHTML = 'Aprovar Orçamento <i class="ti tabler-check ms-2"></i>';
                            btn.classList.add('btn-primary');
                            btn.classList.remove('btn-success');
                        }
                    }

                    function handleApproval(e) {
                        const isEarly = document.getElementById('early_yes').checked;
                        if (isEarly) {
                            e.preventDefault();
                            window.location.href = "{{ route('public.budget.checkout', $budget->uuid) }}";
                            return false;
                        }
                        return true;
                    }

                    // Run on load
                    document.addEventListener('DOMContentLoaded', updateButtonText);
                </script>
                @elseif($budget->status == 'approved')
                <div class="text-center py-5">
                    <div class="avatar avatar-xl bg-label-success m-auto mb-4 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="ti tabler-circle-check fs-1"></i>
                    </div>
                    <h3 class="fw-bold text-success">Orçamento Aprovado!</h3>
                    <p class="text-muted">Aprovado em {{ $budget->approved_at->format('d/m/Y \à\s H:i') }}</p>
                    @if($budget->early_payment !== null)
                    <p class="fw-medium">Preferência de Pagamento:
                        <span class="badge @if($budget->early_payment) bg-label-success @else bg-label-secondary @endif">
                            @if($budget->early_payment) Antecipado @else Na Entrega @endif
                        </span>
                    </p>
                    @endif
                </div>
                @else
                <div class="alert alert-danger p-4 text-center">
                    <h4 class="text-danger fw-bold">Orçamento Rejeitado</h4>
                    <p class="mb-0">Motivo: {{ $budget->rejection_reason }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Rejeição -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('public.budget.reject', $budget->uuid) }}" method="POST">
                @csrf
                <div class="modal-header border-0 mt-2">
                    <h5 class="modal-title fw-bold">Rejeitar Orçamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Desculpe saber que o orçamento não atendeu às suas expectativas. Poderia nos contar o motivo?</p>
                    <textarea name="rejection_reason" class="form-control rounded-3" rows="4" required placeholder="Escreva o motivo aqui..."></textarea>
                </div>
                <div class="modal-footer border-0 mb-3">
                    <button type="button" class="btn btn-label-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Confirmar Rejeição</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- WhatsApp FAB -->
<a href="https://wa.me/{{ preg_replace('/\D/', '', $budget->company->phone ?? '') }}" target="_blank" class="whatsapp-fab">
    <i class="ti tabler-brand-whatsapp"></i>
</a>

@include('content.public.customer-portal.chat-widget')
@endsection