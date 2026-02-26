@extends('layouts/layoutMaster')

@section('title', 'Monitoramento de Erros - Ghotme Master')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center py-3">
        <div>
            <h5 class="card-title mb-0">Radar de Saúde Técnica (Erros de Sistema)</h5>
            <small class="text-muted">{{ $errors->total() }} registros</small>
        </div>
        @if($errors->total() > 0)
        <form action="{{ route('master.errors.clear') }}" method="POST" onsubmit="return confirm('ATENÇÃO: Isso excluirá permanentemente TODOS os logs de erro do sistema. Deseja continuar?')">
            @csrf
            <button type="submit" class="btn btn-label-danger btn-sm">
                <i class="ti tabler-trash-x me-1"></i> Limpar Tudo
            </button>
        </form>
        @endif
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Data/Hora</th>
              <th>Empresa/Usuário</th>
              <th>Erro</th>
              <th>Página (URL)</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            @foreach($errors as $error)
            <tr>
              <td><small>{{ $error->created_at->format('d/m/Y H:i:s') }}</small></td>
              <td>
                <div class="d-flex flex-column">
                  <span class="fw-bold">{{ $error->user->company->name ?? 'N/A' }}</span>
                  <small class="text-muted">{{ $error->user->name ?? 'Visitante' }}</small>
                </div>
              </td>
              <td><span class="text-danger small fw-bold">{{ str($error->message)->limit(50) }}</span></td>
              <td><code class="small">{{ str($error->url)->limit(30) }}</code></td>
              <td class="d-flex gap-1">
                <button class="btn btn-sm btn-icon text-primary" data-bs-toggle="modal" data-bs-target="#errorModal{{ $error->id }}"><i class="ti tabler-eye"></i></button>
                <form action="{{ route('master.errors.destroy', $error->id) }}" method="POST" onsubmit="return confirm('Excluir este log?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-icon text-danger"><i class="ti tabler-trash"></i></button>
                </form>
              </td>
            </tr>

            <!-- Modal Detalhes -->
            <div class="modal fade" id="errorModal{{ $error->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header border-bottom">
                    <h5 class="modal-title">Detalhes do Erro #{{ $error->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Mensagem:</label>
                        <div class="p-3 bg-label-danger rounded text-danger fw-bold">{{ $error->message }}</div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo:</label>
                            <p class="small text-muted">{{ $error->error_type }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Método:</label>
                            <span class="badge bg-label-primary">{{ $error->method }}</span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Stack Trace:</label>
                        <pre class="p-3 bg-dark text-white rounded small overflow-auto" style="max-height: 300px;">{{ $error->stack_trace }}</pre>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        {{ $errors->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>
@endsection
