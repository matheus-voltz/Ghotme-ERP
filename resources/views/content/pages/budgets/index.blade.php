@extends('layouts/layoutMaster')

@section('title', 'Orçamentos')

@section('vendor-style')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
@endsection

@section('vendor-script')
@vite([
    'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('content')
<div class="card">
  <div class="card-header border-bottom d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Orçamentos</h5>
    <a href="{{ route('budgets.create') }}" class="btn btn-primary">Novo Orçamento</a>
  </div>
  <div class="table-responsive p-3">
    <table class="table table-bordered datatables-budgets">
      <thead>
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Status</th>
          <th>Total</th>
          <th>Ações</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<!-- Modais -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Detalhes</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="quickViewContent">
            <div class="text-center p-4"><div class="spinner-border text-primary"></div></div>
        </div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializa DataTable
    var dt = $('.datatables-budgets').DataTable({
        ajax: "{{ route('budgets.data') }}",
        columns: [
            { data: 'id' },
            { data: 'client' },
            { data: 'status' },
            { data: 'total' },
            { data: 'id' }
        ],
        columnDefs: [
            {
                targets: 1, // Cliente Clicável
                render: function(data, type, full) {
                    return '<a href="javascript:void(0)" class="view-client" data-id="'+full.client_id+'">'+data+'</a>';
                }
            },
            {
                targets: 2, // Status
                render: function(data) {
                    var colors = {pending: 'warning', approved: 'success', rejected: 'danger'};
                    return '<span class="badge bg-label-'+(colors[data] || 'secondary')+'">'+data.toUpperCase()+'</span>';
                }
            },
            {
                targets: 3, // Total
                render: function(data) { return 'R$ ' + parseFloat(data).toFixed(2); }
            },
                        {
                            targets: 4, // Ações
                                            render: function(data, type, full) {
                                                var btns = '<div class="d-flex gap-2">' +
                                                       '<button class="btn btn-sm btn-icon btn-label-secondary btn-view" data-id="'+data+'" title="Visualizar"><i class="ti tabler-eye"></i></button>';
                                                
                                                if(full.has_phone) {
                                                    btns += '<button class="btn btn-sm btn-icon btn-label-info btn-whatsapp" data-id="'+data+'" title="WhatsApp"><i class="ti tabler-brand-whatsapp"></i></button>';
                                                }
                                                
                                                if(full.status === 'pending') {
                            
                                    btns += '<button class="btn btn-sm btn-icon btn-label-success btn-approve" data-id="'+data+'" title="Aprovar"><i class="ti tabler-check"></i></button>' +
                                            '<button class="btn btn-sm btn-icon btn-label-danger btn-reject" data-id="'+data+'" title="Reprovar"><i class="ti tabler-x"></i></button>';
                                }
                                
                                btns += '</div>';
                                return btns;
                            }
                        }
                    ]
                });
            
                // Delegar cliques para os botões e links
                $(document).on('click', '.view-client', function() {
                    var id = $(this).data('id');
                    $('#quickViewModal').modal('show');
                    $.get('/clients/' + id + '/quick-view', function(h) { $('#quickViewContent').html(h); });
                });
            
                $(document).on('click', '.btn-view', function() {
                    var id = $(this).data('id');
                    $('#quickViewModal').modal('show');
                    $.get('/budgets/' + id + '/quick-view', function(h) { $('#quickViewContent').html(h); });
                });
            
                $(document).on('click', '.btn-whatsapp', function() {
                    var id = $(this).data('id');
                    $.get('/budgets/' + id + '/whatsapp', function(r) { if(r.success) window.open(r.url, '_blank'); });
                });
            
                $(document).on('click', '.btn-approve', function() {
                    var id = $(this).data('id');
                    if(confirm('Deseja aprovar este orçamento e gerar a OS?')) {
                        $.post('/budgets/' + id + '/convert', { _token: '{{ csrf_token() }}' }, function() {
                            dt.ajax.reload();
                        });
                    }
                });
            
                $(document).on('click', '.btn-reject', function() {
                    var id = $(this).data('id');
                    var reason = prompt('Motivo da rejeição:');
                    if(reason) {
                        $.post('/budgets/' + id + '/status', { 
                            _token: '{{ csrf_token() }}',
                            status: 'rejected',
                            reason: reason 
                        }, function() {
                            dt.ajax.reload();
                        });
                    }
                });
            });
            </script>
            @endsection
            