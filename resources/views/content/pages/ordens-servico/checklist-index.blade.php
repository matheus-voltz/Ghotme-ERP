@extends('layouts/layoutMaster')

@section('title', __('Checklists de Entrada'))

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">{{ __('Histórico de Checklists') }}</h5>
    <a href="{{ route('ordens-servico.checklist.create') }}" class="btn btn-primary">{{ __('Novo Checklist') }}</a>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>{{ __('Veículo') }}</th>
          <th>{{ __('Responsável') }}</th>
          <th>{{ __('Data') }}</th>
          <th>{{ __('Ações') }}</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach($inspections as $inspection)
        <tr>
          <td>#{{ $inspection->id }}</td>
          <td>
            <strong>{{ $inspection->veiculo->placa ?? 'N/A' }}</strong><br>
            <small>{{ $inspection->veiculo->modelo ?? 'Veículo não encontrado' }}</small>
          </td>
          <td>{{ $inspection->user->name }}</td>
          <td>{{ $inspection->created_at->format('d/m/Y H:i') }}</td>
          <td>
            <div class="d-flex gap-2">
              <a href="{{ route('ordens-servico.checklist.show', $inspection->id) }}" class="btn btn-sm btn-icon btn-label-secondary" title="{{ __('Visualizar') }}">
                <i class="ti tabler-eye"></i>
              </a>
              <button type="button" class="btn btn-sm btn-icon btn-label-primary btn-send-email" data-id="{{ $inspection->id }}" title="{{ __('Enviar por E-mail') }}">
                <i class="ti tabler-mail"></i>
              </button>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@section('vendor-style')
@vite([
'resources/assets/vendor/libs/animate-css/animate.scss',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('page-script')
<script>
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-send-email');
    if (btn) {
      e.preventDefault();
      const id = btn.getAttribute('data-id');
      const originalHtml = btn.innerHTML;
      const baseUrl = window.location.origin;

      btn.classList.add('disabled');
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

      fetch(`${baseUrl}/ordens-servico/checklist/${id}/send-email`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(res => {
          const swalConfig = {
            icon: res.success ? 'success' : 'error',
            title: res.success ? "{{ __('Sucesso!') }}" : "{{ __('Ops!') }}",
            text: res.message
          };
          if (typeof Swal !== 'undefined') {
            Swal.fire(swalConfig);
          } else {
            alert(res.message);
          }
        })
        .catch(err => {
          console.error('Error:', err);
          Swal.fire({
            icon: 'error',
            title: "{{ __('Erro!') }}",
            text: "{{ __('Falha na comunicação com o servidor.') }}"
          });
        })
        .finally(() => {
          btn.classList.remove('disabled');
          btn.innerHTML = originalHtml;
        });
    }
  });
</script>
@endsection
@endsection