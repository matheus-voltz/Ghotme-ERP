@php
$configData = Helper::appClasses();
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/commonMaster')

@section('title', 'Checkout - Orçamento #' . $budget->id)

@section('page-style')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --portal-primary: #7367f0;
        --portal-bg: #f8f7fa;
    }

    body {
        background-color: var(--portal-bg) !important;
        font-family: 'Inter', sans-serif !important;
    }

    .checkout-header {
        background: linear-gradient(135deg, #28c76f 0%, #1e9652 100%);
        padding: 4rem 0 5rem;
        color: white;
        text-align: center;
        border-radius: 0 0 3rem 3rem;
        position: relative;
        z-index: 1;
    }

    .checkout-container {
        margin-top: -4rem;
        position: relative;
        z-index: 2;
    }

    .checkout-card {
        border: none;
        border-radius: 1.5rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        background: white;
    }

    .payment-method-item {
        border: 2px solid #eee;
        border-radius: 1rem;
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .payment-method-item:hover {
        border-color: var(--portal-primary);
        background: #fdfdff;
    }

    .payment-input:checked+.payment-method-item {
        border-color: var(--portal-primary);
        background-color: rgba(115, 103, 240, 0.05);
        box-shadow: 0 4px 12px rgba(115, 103, 240, 0.1);
    }
</style>
@endsection

@section('layoutContent')
<!-- Full Width Header -->
<div class="checkout-header w-100">
    <div class="container">
        <h2 class="text-white fw-bold mb-1">Finalizar Pagamento</h2>
        <p class="text-white opacity-75 fs-5">Orçamento #{{ $budget->id }}</p>
    </div>
</div>

<div class="container checkout-container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="checkout-card p-4 p-md-5">

                <!-- Resumo -->
                <div class="alert alert-primary d-flex justify-content-between align-items-center p-4 rounded-3 mb-5 border-0 bg-label-primary">
                    <div>
                        <h6 class="mb-1 text-primary fw-bold">Total a Pagar</h6>
                        <small class="text-primary opacity-75">Pagamento antecipado integral</small>
                    </div>
                    <h3 class="mb-0 fw-bold text-primary">R$ {{ number_format($budget->total, 2, ',', '.') }}</h3>
                </div>

                <form action="{{ route('public.budget.approve', $budget->uuid) }}" method="POST">
                    @csrf
                    <input type="hidden" name="early_payment" value="1">

                    <h5 class="fw-bold mb-4">Selecione a Forma de Pagamento</h5>

                    <div class="d-flex flex-column gap-3 mb-5">
                        @forelse($paymentMethods as $method)
                        <div class="position-relative">
                            <input type="radio" name="payment_method_id" value="{{ $method->id }}" id="pm_{{ $method->id }}" class="payment-input d-none" required>
                            <label for="pm_{{ $method->id }}" class="payment-method-item w-100">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar bg-label-secondary rounded p-2">
                                        <i class="ti tabler-credit-card fs-2 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $method->name }}</h6>
                                        <small class="text-muted">{{ $method->type == 'credit_card' ? 'Cartão de Crédito' : ($method->type == 'pix' ? 'Pagamento Instantâneo' : 'Outro') }}</small>
                                    </div>
                                </div>
                                <div class="form-check-input border-2 border-primary rounded-circle" style="width: 20px; height: 20px;"></div>
                            </label>
                        </div>
                        @empty
                        <div class="text-center py-5 border rounded-3 dashed bg-light">
                            <i class="ti tabler-credit-card-off fs-1 text-muted mb-3"></i>
                            <p class="mb-0 text-muted fw-medium">Nenhuma forma de pagamento disponível no momento.</p>
                        </div>
                        @endforelse
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <a href="{{ route('public.budget.show', $budget->uuid) }}" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="ti tabler-arrow-left me-2"></i> Voltar
                        </a>
                        <button type="submit" class="btn btn-success btn-lg rounded-pill px-5 shadow-lg">
                            Confirmar Pagamento <i class="ti tabler-check ms-2"></i>
                        </button>
                    </div>
                </form>

            </div>

            <div class="text-center mt-4">
                <small class="text-muted"><i class="ti tabler-lock me-1"></i> Pagamento processado em ambiente seguro.</small>
            </div>
        </div>
    </div>
</div>
@endsection