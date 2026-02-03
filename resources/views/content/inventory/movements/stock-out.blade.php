@extends('layouts/layoutMaster')

@section('title', 'Saída de Estoque')

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

    const form = document.getElementById('formStockOut');
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
});
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card border-danger border">
            <div class="card-header border-bottom bg-label-danger">
                <h5 class="card-title mb-0">Registrar Saída (Uso/Baixa)</h5>
            </div>
            <div class="card-body pt-6">
                <form id="formStockOut">
                    @csrf
                    <input type="hidden" name="type" value="out">
                    
                    <div class="mb-6">
                        <label class="form-label" for="inventory_item_id">Item / Peça</label>
                        <select id="inventory_item_id" name="inventory_item_id" class="select2 form-select" required>
                            <option value="">Selecione</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" data-qty="{{ $item->quantity }}">
                                    {{ $item->name }} (Atual: {{ $item->quantity }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-6">
                        <label class="form-label" for="quantity">Quantidade a Retirar</label>
                        <input type="number" id="quantity" name="quantity" class="form-control" placeholder="0" required min="1">
                    </div>

                    <div class="mb-6">
                        <label class="form-label" for="reason">Motivo da Saída</label>
                        <input type="text" id="reason" name="reason" class="form-control" placeholder="Ex: Uso na OS #10, Item danificado, Venda avulsa" required>
                    </div>

                    <button type="submit" class="btn btn-danger w-100">Confirmar Baixa de Estoque</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Observação</h5>
            </div>
            <div class="card-body pt-6">
                <p>O sistema não permitirá a saída de uma quantidade maior do que a disponível em estoque.</p>
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <span class="alert-icon text-warning me-2">
                        <i class="ti tabler-alert-triangle ti-xs"></i>
                    </span>
                    Atenção: A saída de estoque é irreversível no histórico de movimentações.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
