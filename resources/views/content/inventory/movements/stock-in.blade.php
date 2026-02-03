@extends('layouts/layoutMaster')

@section('title', 'Entrada de Estoque')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const select2 = $('.select2');
    if (select2.length) {
        select2.each(function() {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
                placeholder: 'Selecione um item',
                dropdownParent: $this.parent()
            });
        });
    }

    const form = document.getElementById('formStockIn');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            
            fetch("{{ route('inventory.movements.store') }}", {
                method: 'POST',
                body: new URLSearchParams(formData),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Sucesso!', text: data.message, customClass: { confirmButton: 'btn btn-success' } });
                    form.reset();
                    $('.select2').val(null).trigger('change');
                } else {
                    Swal.fire({ icon: 'error', title: 'Erro!', text: data.message, customClass: { confirmButton: 'btn btn-danger' } });
                }
            });
        });
    }

    // Ao selecionar item, carregar preço de custo atual
    $('#inventory_item_id').on('change', function() {
        const selected = $(this).find(':selected');
        const cost = selected.data('cost');
        if (cost) {
            $('#unit_price').val(cost);
        }
    });
});
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Registrar Entrada (Compra)</h5>
            </div>
            <div class="card-body pt-6">
                <form id="formStockIn">
                    @csrf
                    <input type="hidden" name="type" value="in">
                    
                    <div class="mb-6">
                        <label class="form-label" for="inventory_item_id">Item / Peça</label>
                        <select id="inventory_item_id" name="inventory_item_id" class="select2 form-select" required>
                            <option value="">Selecione</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" data-cost="{{ $item->cost_price }}">
                                    {{ $item->name }} (SKU: {{ $item->sku ?? '-' }}) - Atual: {{ $item->quantity }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-6">
                        <div class="col-6">
                            <label class="form-label" for="quantity">Quantidade</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" placeholder="0" required min="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="unit_price">Preço de Custo Unitário (R$)</label>
                            <input type="number" step="0.01" id="unit_price" name="unit_price" class="form-control" placeholder="0.00">
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="form-label" for="reason">Motivo / Documento</label>
                        <input type="text" id="reason" name="reason" class="form-control" placeholder="Ex: Nota Fiscal #123, Reposição de estoque" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Confirmar Entrada</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Dicas</h5>
            </div>
            <div class="card-body pt-6">
                <ul class="list-unstyled">
                    <li class="mb-4 d-flex align-items-start">
                        <i class="ti tabler-info-circle text-primary me-2 mt-1"></i>
                        <span>Ao registrar uma entrada, o sistema atualizará automaticamente a quantidade total em estoque.</span>
                    </li>
                    <li class="mb-4 d-flex align-items-start">
                        <i class="ti tabler-trending-up text-success me-2 mt-1"></i>
                        <span>O preço de custo informado será salvo como o novo custo padrão deste item.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
