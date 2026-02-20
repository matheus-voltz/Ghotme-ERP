@extends('layouts/layoutMaster')

@section('title', 'Gestão de Comissões')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Equipe /</span> Comissões
</h4>

<!-- KPI Cards -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card bg-label-warning p-3">
            <h5 class="text-warning mb-0">Total Pendente: R$ {{ number_format($totals['pending'], 2, ',', '.') }}</h5>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card bg-label-success p-3">
            <h5 class="text-success mb-0">Total Pago: R$ {{ number_format($totals['paid'], 2, ',', '.') }}</h5>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Colaborador</label>
                <select name="user_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Pago</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabela -->
<div class="card">
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Data</th>
          <th>Colaborador</th>
          <th>Descrição</th>
          <th>Base (R$)</th>
          <th>Comissão (R$)</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @forelse($commissions as $comm)
        <tr id="row-{{ $comm->id }}">
          <td>{{ $comm->created_at->format('d/m/Y') }}</td>
          <td><strong>{{ $comm->user->name }}</strong> <small class="text-muted">({{ round($comm->percentage) }}%)</small></td>
          <td>{{ $comm->description }}</td>
          <td>R$ {{ number_format($comm->base_amount, 2, ',', '.') }}</td>
          <td class="fw-bold">R$ {{ number_format($comm->commission_amount, 2, ',', '.') }}</td>
          <td>
            <span class="badge bg-label-{{ $comm->status == 'paid' ? 'success' : 'warning' }}">
              {{ $comm->status == 'paid' ? 'Pago' : 'Pendente' }}
            </span>
          </td>
          <td>
            @if($comm->status == 'pending')
            <button class="btn btn-sm btn-success btn-pay" data-id="{{ $comm->id }}">
                <i class="ti tabler-check"></i> Pagar
            </button>
            @else
            <small class="text-muted">{{ $comm->paid_at->format('d/m H:i') }}</small>
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center p-4">Nenhuma comissão encontrada.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-pay').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            
            Swal.fire({
                title: 'Confirmar pagamento?',
                text: "Esta comissão será marcada como paga.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, pagar',
                customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
                buttonsStyling: false
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(`/team/commissions/${id}/pay`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            location.reload();
                        }
                    });
                }
            });
        });
    });
});
</script>
@endsection
