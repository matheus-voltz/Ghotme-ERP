@extends('layouts/layoutMaster')

@section('title', 'Ajustes de Inventário')

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

    const form = document.getElementById('formAdjustment');
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
                    $('#current-qty-display').text('---');
                    $('#diff-display').text('0').removeClass('text-success text-danger');
                } else {
                    Swal.fire({ icon: 'error', title: 'Erro!', text: data.message, customClass: { confirmButton: 'btn btn-danger' } });
                }
            });
        });
    }

    // Exibir quantidade atual ao selecionar
    $('#inventory_item_id').on('change', function() {
        const selected = $(this).find(':selected');
        const qty = selected.data('qty');
        if (qty !== undefined) {
            $('#current-qty-display').text(qty);
            $('#new_quantity').val(qty);
            $('#diff-display').text('0').removeClass('text-success text-danger');
        } else {
            $('#current-qty-display').text('---');
            $('#diff-display').text('0');
        }
    });

    // Calcular diferença ao digitar nova quantidade
    $('#new_quantity').on('input', function() {
        const current = parseInt($('#current-qty-display').text()) || 0;
        const newVal = parseInt($(this).val()) || 0;
        const diff = newVal - current;
        
        const diffDisplay = $('#diff-display');
        if (diff > 0) {
            diffDisplay.text('+' + diff).removeClass('text-danger').addClass('text-success');
        } else if (diff < 0) {
            diffDisplay.text(diff).removeClass('text-success').addClass('text-danger');
        } else {
            diffDisplay.text('0').removeClass('text-success text-danger');
        }
    });
});
</script>
@endsection

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Ajuste Manual de Estoque (Inventário)</h5>
            </div>
            <div class="card-body pt-6">
                <form id="formAdjustment">
                    @csrf
                    <input type="hidden" name="type" value="adjustment">
                    
                    <div class="mb-6">
                        <label class="form-label" for="inventory_item_id">Item / Peça</label>
                        <select id="inventory_item_id" name="inventory_item_id" class="select2 form-select" required>
                            <option value="">Selecione</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" data-qty="{{ $item->quantity }}">
                                    {{ $item->name }} (SKU: {{ $item->sku ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-6">
                        <div class="col-4 text-center">
                            <label class="form-label d-block">Qtd. Atual</label>
                            <h3 id="current-qty-display" class="mt-2 text-primary">---</h3>
                        </div>
                        <div class="col-4">
                            <label class="form-label" for="new_quantity">Nova Qtd.</label>
                            <input type="number" id="new_quantity" name="new_quantity" class="form-control form-control-lg text-center" placeholder="0" required min="0">
                        </div>
                        <div class="col-4 text-center">
                            <label class="form-label d-block">Diferença</label>
                            <h3 id="diff-display" class="mt-2">0</h3>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="form-label" for="reason">Motivo do Ajuste</label>
                        <select id="reason" name="reason_base" class="form-select mb-2" required>
                            <option value="Correção de Inventário">Correção de Inventário (Contagem manual)</option>
                            <option value="Item Danificado/Vencido">Item Danificado / Vencido</option>
                            <option value="Perda/Extravio">Perda / Extravio</option>
                            <option value="Outro">Outro (especificar abaixo)</option>
                        </select>
                        <textarea id="reason_detail" name="reason_detail" class="form-control" placeholder="Detalhes adicionais..." rows="2"></textarea>
                    </div>

                    <button type="submit" class="btn btn-warning w-100">Aplicar Ajuste de Inventário</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Quando usar o Ajuste?</h5>
            </div>
            <div class="card-body pt-6">
                <p>O <strong>Ajuste de Inventário</strong> deve ser usado para sincronizar o sistema com a realidade física da oficina, como em casos de:</p>
                <ul>
                    <li class="mb-2">Contagens periódicas de estoque.</li>
                    <li class="mb-2">Perdas acidentais de peças.</li>
                    <li class="mb-2">Erros de lançamento anteriores.</li>
                </ul>
                <div class="alert alert-info py-2 px-3 mt-4">
                    <small><i class="ti tabler-info-circle me-1"></i> Para compras, use sempre o módulo de <strong>Entrada</strong> para registrar o custo corretamente.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection