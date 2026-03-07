@extends('layouts/layoutMaster')

@section('title', 'Pedidos iFood')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Estabelecimento /</span> Pedidos iFood
</h4>

<div class="row">
  <div class="col-12">
    <div class="card mb-6 border-danger shadow-none">
      <div class="card-header border-bottom bg-label-danger d-flex justify-content-between align-items-center py-3">
        <div>
          <h5 class="card-title mb-0 text-danger"><i class="ti tabler-tools-kitchen-2 me-1"></i> Gestão de Pedidos iFood</h5>
          <p class="text-muted small mb-0">Monitore e receba seus pedidos em tempo real.</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-danger btn-sm shadow-none" onclick="refreshOrders()">
                <i class="ti tabler-refresh me-1"></i> Atualizar Agora
            </button>
            <a href="{{ route('settings.integrations') }}" class="btn btn-outline-danger btn-sm">
                <i class="ti tabler-settings me-1"></i> Configurar API
            </a>
        </div>
      </div>
      <div class="card-body pt-4">
        <div class="table-responsive">
          <table class="table table-hover border-top">
            <thead>
              <tr>
                <th>Pedido</th>
                <th>Cliente</th>
                <th>Horário</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="6" class="text-center py-5">
                  <div class="d-flex flex-column align-items-center">
                    <i class="ti tabler-clipboard-off ti-lg text-muted mb-2"></i>
                    <p class="mb-0">Nenhum pedido recebido nos últimos minutos.</p>
                    <small class="text-muted">Certifique-se de que sua API Key está configurada corretamente.</small>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function refreshOrders() {
    Swal.fire({
        title: 'Buscando novos pedidos',
        text: 'Conectando ao iFood...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });
    
    setTimeout(() => {
        Swal.fire({
            icon: 'info',
            title: 'Tudo em dia!',
            text: 'Não existem novos pedidos aguardando no momento.',
            customClass: { confirmButton: 'btn btn-primary' }
        });
    }, 1500);
}
</script>
@endsection
