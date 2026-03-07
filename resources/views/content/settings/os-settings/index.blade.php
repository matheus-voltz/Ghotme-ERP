@extends('layouts/layoutMaster')

@section('title', get_current_niche() === 'food_service' ? 'Configurações de Pedido' : 'Configurações da OS')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Configurações /</span> {{ get_current_niche() === 'food_service' ? 'Pedidos e Comandas' : 'Ordem de Serviço' }}
</h4>

<div class="row">
    <div class="col-md-12 col-lg-8">
        <div class="card mb-4">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Parâmetros de {{ get_current_niche() === 'food_service' ? 'Venda e Impressão' : 'Ordem de Serviço e Orçamentos' }}</h5>
                <i class="icon-base ti tabler-settings fs-2 text-muted"></i>
            </div>
            <div class="card-body pt-4">
                <form id="formAppSettings">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Prefixo dos Pedidos</label>
                            <div class="input-group">
                                <span class="input-group-text bg-label-secondary border-0"><i class="ti tabler-hash"></i></span>
                                <input type="text" name="os_prefix" class="form-control" value="{{ $settings->os_prefix ?? (get_current_niche() === 'food_service' ? 'PED-' : 'OS-') }}" placeholder="Ex: PED-" />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Próximo Número (Sequencial)</label>
                            <input type="number" name="os_next_number" class="form-control" value="{{ $settings->os_next_number ?? 1 }}" />
                        </div>
                        
                        @if(get_current_niche() !== 'food_service')
                        <div class="col-md-12">
                            <label class="form-label">Validade Padrão de Orçamentos (dias)</label>
                            <div class="input-group">
                                <input type="number" name="budget_validity_days" class="form-control" value="{{ $settings->budget_validity_days ?? 7 }}" />
                                <span class="input-group-text">dias</span>
                            </div>
                        </div>
                        @endif

                        <div class="col-md-12">
                            <label class="form-label">Mensagem de Rodapé (Cupom/Comanda)</label>
                            <textarea name="os_terms" class="form-control" rows="8" placeholder="{{ get_current_niche() === 'food_service' ? 'Ex: Obrigado pela preferência! Volte sempre.' : 'Digite aqui os termos de garantia...' }}">{{ $settings->os_terms }}</textarea>
                            <small class="text-muted">Este texto será exibido automaticamente no final do seu cupom de venda ou comanda de cozinha.</small>
                        </div>
                    </div>
                    <div class="mt-4 pt-2">
                        <button type="submit" class="btn btn-primary d-flex align-items-center">
                            <i class="ti tabler-device-floppy me-1"></i> Salvar Configurações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-12 col-lg-4">
        <div class="card bg-label-primary border-0 shadow-none mb-4">
            <div class="card-body">
                <h6><i class="ti tabler-info-circle me-1"></i> Dica de Uso</h6>
                <p class="small mb-0">As configurações definidas aqui afetam como os seus pedidos são numerados e qual mensagem o seu cliente verá no final do ticket impresso.</p>
            </div>
        </div>

        @if(get_current_niche() === 'food_service')
        <div class="card bg-label-info border-0 shadow-none">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <i class="ti tabler-printer me-2"></i>
                    <h6 class="mb-0">Impressão Térmica</h6>
                </div>
                <p class="small mb-3">Para configurar o nome das suas impressoras de balcão e cozinha, utilize o menu de Configurações PDV.</p>
                <a href="{{ route('settings.food-service') }}" class="btn btn-info btn-sm w-100">Configurar Impressoras</a>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('formAppSettings').onsubmit = function(e) {
        e.preventDefault();
        fetch("{{ route('settings.os-settings.update') }}", {
            method: 'POST',
            body: new URLSearchParams(new FormData(this)),
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        }).then(res => res.json()).then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Sucesso!', text: data.message, customClass: { confirmButton: 'btn btn-success' } });
            }
        });
    };
});
</script>
@endsection
