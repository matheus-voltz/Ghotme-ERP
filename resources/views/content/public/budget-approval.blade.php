@php
$customizerHidden = 'customizer-hide';
@endphp

@extends('layouts/layoutPublic')

@section('title', 'Aprovação de Orçamento')

@section('page-style')
<style>
    /* Força o fundo em todo o body e wrapper */
    body { background-color: #f4f3ff !important; }
    .budget-wrapper { min-height: 100vh; background-color: #f4f3ff !important; padding-bottom: 3rem; }
    
    /* Remove o botão de engrenagem do Customizer do Vuexy */
    .template-customizer-open-btn { display: none !important; }
    
    .budget-header { background: linear-gradient(135deg, #7367f0 0%, #9e95f5 100%); color: white; border-radius: 0.5rem 0.5rem 0 0; }
    .status-badge { font-size: 1.2rem; padding: 0.5rem 1rem; }
    .company-banner { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid #e6e5f3; padding: 2.5rem 0; margin-bottom: 2rem; }
    .card { border: none; border-radius: 1rem; box-shadow: 0 0.5rem 1.5rem rgba(115, 103, 240, 0.08) !important; }
</style>
@endsection

@section('content')
<!-- Card do Orçamento -->
<div class="card">
    <div class="card-body">
        <!-- Banner da Empresa -->
        <div class="text-center mb-4">
            @if($budget->company->logo_path)
                <img src="{{ asset('storage/' . $budget->company->logo_path) }}" alt="Logo" class="mb-3" style="max-height: 60px;">
            @endif
            <h4 class="mb-1 fw-bold">{{ $budget->company->name ?? 'Oficina' }}</h4>
            <span class="badge bg-label-primary">Orçamento #{{ $budget->id }}</span>
        </div>

        <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><strong>Oficina:</strong> {{ $budget->company->name ?? 'Nossa Oficina' }}</h6>
                            <p class="mb-1">{{ $budget->company->address ?? '' }}</p>
                            <p class="mb-1">{{ $budget->company->phone ?? '' }}</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6><strong>Cliente:</strong> {{ $budget->client->name }}</h6>
                            <p class="mb-1">{{ $budget->veiculo->marca }} {{ $budget->veiculo->modelo }} - {{ $budget->veiculo->placa }}</p>
                            <p class="mb-1">Válido até: {{ $budget->valid_until ? $budget->valid_until->format('d/m/Y') : 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <h5 class="my-4">Serviços & Peças</h5>
                    <div class="table-responsive border rounded">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Descrição</th>
                                    <th class="text-center">Qtd</th>
                                    <th class="text-end">Preço Unit.</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($budget->items as $item)
                                <tr>
                                    <td>{{ $item->service->name ?? $item->description }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                                    <td class="text-end">R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                                @foreach($budget->parts as $part)
                                <tr>
                                    <td>{{ $part->part->name ?? $part->description }} (Peça)</td>
                                    <td class="text-center">{{ $part->quantity }}</td>
                                    <td class="text-end">R$ {{ number_format($part->price, 2, ',', '.') }}</td>
                                    <td class="text-end">R$ {{ number_format($part->price * $part->quantity, 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Total Geral:</th>
                                    <th class="text-end text-primary h5">R$ {{ number_format($budget->total, 2, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($budget->description)
                    <div class="mt-4 p-3 bg-light rounded">
                        <h6>Observações:</h6>
                        <p class="mb-0">{{ $budget->description }}</p>
                    </div>
                    @endif

                    @if($budget->status == 'pending')
                    <div class="mt-5 d-flex gap-3 justify-content-center">
                        <button type="button" class="btn btn-label-danger btn-lg" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="ti tabler-x me-1"></i> Rejeitar
                        </button>
                        <form action="{{ route('public.budget.approve', $budget->uuid) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-lg px-5">
                                <i class="ti tabler-check me-1"></i> Aprovar Orçamento
                            </button>
                        </form>
                    </div>
                    @elseif($budget->status == 'approved')
                    <div class="alert alert-success mt-5 text-center">
                        <i class="ti tabler-circle-check-filled me-2"></i>
                        Este orçamento foi aprovado em {{ $budget->approved_at->format('d/m/Y \à\s H:i') }}.
                    </div>
                    @else
                    <div class="alert alert-danger mt-5">
                        <h6>Orçamento Rejeitado</h6>
                        <p class="mb-0">Motivo: {{ $budget->rejection_reason }}</p>
                    </div>
                    @endif
    </div>
</div>

<!-- Modal Rejeição -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('public.budget.reject', $budget->uuid) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Rejeitar Orçamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Poderia nos informar o motivo da rejeição? Isso nos ajuda a melhorar nossos serviços.</p>
                    <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Ex: Valor acima do esperado, não farei o serviço agora..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar Rejeição</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
