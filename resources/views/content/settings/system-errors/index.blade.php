@extends('layouts/layoutMaster')

@section('title', 'Logs de Erros do Sistema')

@section('vendor-script')
@vite(['resources/assets/vendor/libs/jquery/jquery.js'])
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Logs de Erros do Banco de Dados</h5>
        <form action="{{ route('settings.system-errors.clear') }}" method="POST" onsubmit="return confirm('Deseja realmente limpar todos os logs?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Limpar Todos os Logs</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuário</th>
                    <th>URL / Método</th>
                    <th>Mensagem</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($errors as $error)
                <tr>
                    <td>#{{ $error->id }}</td>
                    <td>{{ $error->user_id ?? 'Convidado' }}</td>
                    <td>
                        <small class="badge bg-label-secondary">{{ $error->method }}</small><br>
                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($error->url, 40) }}</small>
                    </td>
                    <td>
                        <div class="text-danger fw-bold" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $error->message }}
                        </div>
                    </td>
                    <td>{{ $error->created_at->format('d/m H:i:s') }}</td>
                    <td>
                        <button class="btn btn-sm btn-icon btn-label-primary view-error" data-id="{{ $error->id }}">
                            <i class="ti tabler-eye"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Nenhum erro registrado. ✨</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $errors->links() }}
    </div>
</div>

<!-- Modal Detalhes do Erro -->
<div class="modal fade" id="errorDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Erro Técnico</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="errorModalContent">
                    <!-- Preenchido via JS -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    $(document).on('click', '.view-error', function() {
        const id = $(this).data('id');
        $('#errorDetailModal').modal('show');
        $('#errorModalContent').html('<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>');

        $.get('/settings/system-errors/' + id, function(data) {
            let html = `
                <div class="row">
                    <div class="col-12 mb-4">
                        <h6><strong>Mensagem:</strong></h6>
                        <div class="alert alert-danger p-3">${data.message}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6><strong>URL:</strong> ${data.url}</h6>
                        <h6><strong>Método:</strong> ${data.method}</h6>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6><strong>Data:</strong> ${new Date(data.created_at).toLocaleString()}</h6>
                        <h6><strong>Tipo:</strong> ${data.error_type}</h6>
                    </div>
                    <div class="col-12 mb-4">
                        <h6><strong>Dados Enviados (Request):</strong></h6>
                        <pre class="bg-light p-3 rounded"><code>${JSON.stringify(data.request_data, null, 2)}</code></pre>
                    </div>
                    <div class="col-12">
                        <h6><strong>Stack Trace (Onde quebrou):</strong></h6>
                        <pre class="bg-dark text-white p-3 rounded small" style="max-height: 300px; overflow-y: scroll;"><code>${data.stack_trace}</code></pre>
                    </div>
                </div>
            `;
            $('#errorModalContent').html(html);
        });
    });
});
</script>
@endsection