<div>
    <div class="alert alert-primary d-flex align-items-center mb-4" role="alert">
        <span class="alert-icon rounded-circle p-1 me-2"><i class="ti tabler-info-circle"></i></span>
        <span>Configure aqui quais itens do estoque são consumidos quando este produto é vendido.</span>
    </div>

    <div class="row g-2 mb-4 align-items-end">
        <div class="col-md-7">
            <label class="form-label">Selecionar Ingrediente / Insumo</label>
            <select wire:model="selectedIngredientId" class="form-select">
                <option value="">Selecione um item...</option>
                @foreach($availableIngredients as $item)
                    <option value="{{ $item->id }}">{{ $item->name }} (Atual: {{ $item->quantity }} {{ $item->unit }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Qtd. Consumida</label>
            <input type="number" step="0.001" wire:model="quantity" class="form-control" placeholder="1">
        </div>
        <div class="col-md-2">
            <button type="button" wire:click="addIngredient" class="btn btn-primary w-100">
                <i class="ti tabler-plus"></i>
            </button>
        </div>
    </div>

    <div class="table-responsive border rounded">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Ingrediente</th>
                    <th class="text-center">Qtd</th>
                    <th class="text-center">Unidade</th>
                    <th class="text-end">Custo Un.</th>
                    <th class="text-end">Custo Total</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ingredients as $row)
                @php $rowCost = $row->quantity * ($row->ingredient->cost_price ?? 0); @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="fw-bold text-heading">{{ $row->ingredient->name }}</span>
                        </div>
                    </td>
                    <td class="text-center fw-bold text-primary">{{ number_format($row->quantity, 3, ',', '.') }}</td>
                    <td class="text-center small">{{ $row->ingredient->unit }}</td>
                    <td class="text-end text-muted small">R$ {{ number_format($row->ingredient->cost_price ?? 0, 2, ',', '.') }}</td>
                    <td class="text-end fw-bold">R$ {{ number_format($rowCost, 2, ',', '.') }}</td>
                    <td class="text-end">
                        <button type="button" wire:click="removeIngredient({{ $row->id }})" class="btn btn-sm btn-icon btn-label-danger">
                            <i class="ti tabler-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        Nenhum ingrediente configurado. Este item será baixado apenas como unidade simples.
                    </td>
                </tr>
                @endforelse
            </tbody>
            @if(count($ingredients) > 0)
            <tfoot class="table-light border-top">
                <tr>
                    <td colspan="4" class="text-end fw-bold py-3 text-uppercase">Custo Total de Preparo:</td>
                    <td class="text-end fw-bold text-primary fs-5 py-3">R$ {{ number_format($totalCost, 2, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
