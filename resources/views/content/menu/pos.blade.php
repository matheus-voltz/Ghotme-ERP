@extends('layouts/layoutMaster')

@section('title', 'Venda Rápida - PDV')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/animate-css/animate.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/select2/select2.scss'
])
<style>
    .pos-item-card {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid #eee;
    }
    .pos-item-card:hover {
        transform: scale(1.03);
        border-color: #7367f0;
        box-shadow: 0 8px 15px rgba(115, 103, 240, 0.1);
    }
    .pos-img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 8px 8px 0 0;
    }
    .category-tab {
        border-radius: 20px !important;
        margin-right: 10px;
        padding: 8px 20px !important;
    }
    .category-tab.active {
        background-color: #7367f0 !important;
        color: white !important;
    }
</style>
@endsection

@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
    'resources/assets/vendor/libs/select2/select2.js'
])
@endsection

@section('content')
<div class="row g-4">
    <!-- Esquerda: Seleção de Produtos -->
    <div class="col-md-12">
        <div class="card mb-4 border-0 shadow-none bg-transparent">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">🍔 Balcão de Vendas</h4>
                    <div style="width: 300px">
                        <select id="client_select" class="select2 form-select">
                            <option value="">Consumidor Final</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Tabs de Categorias -->
                <ul class="nav nav-pills mb-4 overflow-auto flex-nowrap pb-2" id="pills-tab" role="tablist">
                    @foreach($categories as $index => $cat)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link category-tab {{ $index == 0 ? 'active' : '' }} border" id="tab-{{ $cat->id }}" data-bs-toggle="pill" data-bs-target="#cat-{{ $cat->id }}" type="button">
                            <i class="ti {{ $cat->icon ?? 'tabler-category' }} me-1"></i> {{ $cat->name }}
                        </button>
                    </li>
                    @endforeach
                </ul>

                <!-- Grid de Produtos -->
                <div class="tab-content p-0" id="pills-tabContent">
                    @foreach($categories as $index => $cat)
                    <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="cat-{{ $cat->id }}" role="tabpanel">
                        <div class="row g-4">
                            @forelse($cat->items as $item)
                            <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                <div class="card h-100 pos-item-card shadow-sm" onclick="quickSale({{ $item->id }}, '{{ $item->name }}', {{ $item->selling_price }})">
                                    <img src="{{ $item->mainImage ? asset('storage/'.$item->mainImage->path) : asset('assets/img/elements/food-placeholder.png') }}" class="pos-img">
                                    <div class="card-body p-3 text-center">
                                        <h6 class="mb-1 text-truncate">{{ $item->name }}</h6>
                                        <span class="fw-bold text-primary">R$ {{ number_format($item->selling_price, 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12 text-center py-5">
                                <p class="text-muted">Nenhum item nesta categoria.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#client_select').select2({
        placeholder: 'Selecione o Cliente (Opcional)'
    });
});

function quickSale(id, name, price) {
    const clientId = $('#client_select').val();
    
    Swal.fire({
        title: 'Confirmar Pedido',
        html: `Deseja lançar <b>1x ${name}</b>?<br><br><span class="h4 text-success">Total: R$ ${parseFloat(price).toFixed(2).replace('.', ',')}</span>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, Finalizar!',
        cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            // Simulando a criação de uma OS simplificada
            return fetch('{{ route("ordens-servico.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    client_id: clientId || 1, // Fallback para ID 1 se vazio
                    veiculo_id: null, // Sistema criará um objeto 'Pedido' genérico se nulo
                    status: 'finalized',
                    description: 'Venda via PDV Rápido',
                    parts: {
                        [id]: { selected: true, price: price, quantity: 1 }
                    }
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Erro ao processar venda');
                return response.json();
            })
            .catch(error => {
                Swal.showValidationMessage(`Falha: ${error}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Venda Realizada!',
                text: 'O estoque dos ingredientes foi baixado automaticamente.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}
</script>
@endsection
