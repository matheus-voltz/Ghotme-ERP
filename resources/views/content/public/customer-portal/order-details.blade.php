@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutPublic')

@section('title', 'Detalhes da Ordem de Serviço')

@section('page-style')
<!-- Google Fonts: Inter -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root {
        --portal-primary: #7367f0;
        --portal-bg: #f8f7fa;
    }

    [data-bs-theme="dark"] {
        --portal-bg: #2f3349;
    }

    body {
        background-color: var(--portal-bg) !important;
        font-family: 'Inter', sans-serif !important;
    }

    .detail-card {
        border: none;
        border-radius: 2rem;
        box-shadow: 0 15px 50px rgba(115, 103, 240, 0.1);
        overflow: hidden;
        background: var(--bs-card-bg, white);
    }

    .os-header {
        background: linear-gradient(135deg, #7367f0 0%, #4831d4 100%);
        padding: 3rem 2rem;
        color: white;
    }

    .status-badge-lg {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 0.75rem 1.5rem;
        border-radius: 1rem;
        font-weight: 600;
        font-size: 1.1rem;
        color: white;
    }

    .timeline-container {
        position: relative;
        padding-left: 3rem;
    }

    .timeline-container::before {
        content: '';
        position: absolute;
        left: 0.75rem;
        top: 0;
        bottom: 0;
        width: 3px;
        background: rgba(0, 0, 0, 0.05);
        border-radius: 3px;
    }

    .timeline-point {
        position: relative;
        margin-bottom: 3rem;
    }

    .timeline-point::before {
        content: '';
        position: absolute;
        left: -2.75rem;
        top: 0.25rem;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        background: var(--bs-card-bg, white);
        border: 4px solid rgba(0, 0, 0, 0.05);
        z-index: 2;
        transition: all 0.3s ease;
    }

    .timeline-point.active::before {
        border-color: var(--portal-primary);
        box-shadow: 0 0 0 5px rgba(115, 103, 240, 0.15);
    }

    .timeline-point.completed::before {
        background: var(--portal-primary);
        border-color: var(--portal-primary);
    }

    .item-list-row {
        padding: 1rem;
        border-radius: 1rem;
        margin-bottom: 0.5rem;
        transition: background 0.2s;
    }

    .item-list-row:hover {
        background: rgba(0, 0, 0, 0.02);
    }

    .whatsapp-fab {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
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
</style>
@endsection

@section('content')
<div class="container py-5">
    <!-- Navegação Superior -->
    <div class="mb-5 d-flex align-items-center">
        <a href="{{ route('customer.portal.index', $order->client->uuid) }}" class="btn btn-sm btn-label-primary rounded-pill px-4">
            <i class="ti tabler-arrow-left me-2"></i> Voltar ao Meu Portal
        </a>
    </div>

    <div class="detail-card">
        <!-- Banner de Ordem -->
        <div class="os-header">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <h6 class="text-white opacity-75 text-uppercase fw-bold mb-2">Ordem de Serviço</h6>
                    <h1 class="text-white fw-bold mb-3">Protocolo #{{ $order->id }}</h1>
                    <div class="d-flex align-items-center">
                        <div class="p-2 bg-white rounded-3 me-3">
                            <i class="ti tabler-car-suv text-primary fs-3"></i>
                        </div>
                        <div>
                            <h4 class="text-white mb-0 fw-600">{{ $order->veiculo->marca }} {{ $order->veiculo->modelo }}</h4>
                            <span class="text-white opacity-75 fs-6">{{ $order->veiculo->placa }} • Ano {{ $order->veiculo->ano ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-4 mt-md-0">
                    <div class="status-badge-lg d-inline-block">
                        <i class="ti tabler-loader-2 spin me-2"></i>
                        @php
                        $statusText = $order->status;
                        switch($order->status) {
                        case 'pending': $statusText = 'Aguardando Início'; break;
                        case 'awaiting_approval': $statusText = 'Aguardando Aprovação'; break;
                        case 'in_progress': $statusText = 'Em Manutenção'; break;
                        case 'testing': $statusText = 'Testes Finais'; break;
                        case 'cleaning': $statusText = 'Higienização'; break;
                        case 'completed': $statusText = 'Pronto para Retirada'; break;
                        case 'paid': $statusText = 'Pago / Finalizado'; break;
                        }
                        @endphp
                        {{ $statusText }}
                    </div>
                    <p class="text-white opacity-50 mt-2 small">Última atualização: {{ $order->updated_at->format('H:i - d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <div class="card-body p-4 p-lg-5">
            <div class="row g-5">
                <!-- Coluna: Acompanhamento -->
                <div class="col-lg-5">
                    <h5 class="fw-bold mb-5"><i class="ti tabler-timeline me-2 text-primary"></i>Linha do Tempo do Serviço</h5>

                    <div class="timeline-container">
                        @php
                        $steps = ['pending', 'awaiting_approval', 'in_progress', 'testing', 'cleaning', 'completed', 'paid'];
                        $currentStep = array_search($order->status, $steps);
                        @endphp

                        <div class="timeline-point @if($currentStep >= 0) completed @endif">
                            <h6 class="fw-bold mb-1">Check-in Realizado</h6>
                            <p class="text-muted small">O seu veículo deu entrada com sucesso na oficina.</p>
                        </div>

                        <div class="timeline-point @if($currentStep > 1) completed @elseif($currentStep == 1) active @endif">
                            <h6 class="fw-bold mb-1">Aprovação do Orçamento</h6>
                            <p class="text-muted small">Aguardando sua autorização para iniciar o serviço.</p>
                        </div>

                        <div class="timeline-point @if($currentStep > 2) completed @elseif($currentStep == 2) active @endif">
                            <h6 class="fw-bold mb-1">Execução de Serviços</h6>
                            <p class="text-muted small">Nossos técnicos estão trabalhando no seu veículo neste momento.</p>
                        </div>

                        <div class="timeline-point @if($currentStep > 3) completed @elseif($currentStep == 3) active @endif">
                            <h6 class="fw-bold mb-1">Fase de Testes</h6>
                            <p class="text-muted small">Realizamos testes rigorosos para garantir sua segurança total.</p>
                        </div>

                        <div class="timeline-point @if($currentStep >= 5) completed @elseif($currentStep >= 4 && $currentStep < 6) active @endif">
                            <h6 class="fw-bold mb-1">Finalização e Entrega</h6>
                            <p class="text-muted small">O veículo está sendo preparado para você vir buscar.</p>
                        </div>
                    </div>
                </div>

                <!-- Coluna: Detalhes Técnicos -->
                <div class="col-lg-7">
                    <div class="p-4 bg-light rounded-4 border-dashed mb-5">
                        <h5 class="fw-bold mb-4">Relatório do Mecânico</h5>
                        <p class="text-muted mb-0 lh-lg">
                            {{ $order->description ?: 'Nenhuma observação técnica adicional para este serviço.' }}
                        </p>
                    </div>

                    <h5 class="fw-bold mb-4">Itens da Ordem de Serviço</h5>
                    <div class="item-list">
                        @foreach($order->items as $item)
                        <div class="item-list-row d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="p-2 bg-label-primary rounded-3 me-3">
                                    <i class="ti tabler-settings"></i>
                                </div>
                                <span>{{ $item->service->name ?? $item->description }}</span>
                            </div>
                            <span class="fw-bold">R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</span>
                        </div>
                        @endforeach

                        @foreach($order->parts as $part)
                        <div class="item-list-row d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="p-2 bg-label-secondary rounded-3 me-3">
                                    <i class="ti tabler-package"></i>
                                </div>
                                <span>{{ $part->part->name ?? $part->description }}</span>
                            </div>
                            <span class="fw-bold">R$ {{ number_format($part->price * $part->quantity, 2, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-5 p-4 bg-primary rounded-4 text-white d-flex justify-content-between align-items-center shadow">
                        <div>
                            <h5 class="text-white mb-0 fw-bold">Investimento Total</h5>
                            <small class="opacity-75">Serviços + Peças autorizadas</small>
                        </div>
                        <h2 class="text-white mb-0 fw-bold">R$ {{ number_format($order->total, 2, ',', '.') }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Contact -->
    <a href="https://wa.me/{{ preg_replace('/\D/', '', $order->company->phone ?? '') }}" target="_blank" class="whatsapp-fab">
        <i class="ti tabler-brand-whatsapp"></i>
    </a>
    @endsection