@extends('layouts/layoutMaster')

@section('title', __('Budgets'))

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
        <h5 class="card-title mb-0">{{ __('Budgets') }}</h5>
        <a href="{{ route('budgets.create') }}" class="btn btn-primary">{{ __('Add') }} {{ __('Budget') }}</a>
    </div>
    <div class="table-responsive p-3">
        <table class="table table-bordered datatables-budgets">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>Total</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Modal Unificado -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="quickViewTitle">{{ __('Details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="quickViewContent">
                <div class="text-center p-4">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tableEl = $('.datatables-budgets');
        if (tableEl.length) {
            var dt = tableEl.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('budgets.data') }}",
                    data: {
                        status: "{{ $status ?? '' }}"
                    }
                },
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'client'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'total'
                    },
                    {
                        data: 'id'
                    }
                ],
                columnDefs: [{
                        targets: 1, // Cliente
                        render: function(data, type, full) {
                            return '<a href="javascript:void(0)" class="view-client fw-bold" data-id="' + full.client_id + '">' + data + '</a>';
                        }
                    },
                    {
                        targets: 2, // Status
                        render: function(data) {
                            var colors = {
                                pending: 'warning',
                                approved: 'success',
                                rejected: 'danger'
                            };
                            var statusTranslations = {
                                pending: "{{ __('Pending') }}",
                                approved: "{{ __('Approved') }}",
                                rejected: "{{ __('Rejected') }}"
                            };
                            return '<span class="badge bg-label-' + (colors[data] || 'secondary') + '">' + (statusTranslations[data] || data).toUpperCase() + '</span>';
                        }
                    },
                    {
                        targets: 3, // Total
                        render: function(data) {
                            return 'R$ ' + parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        targets: 4, // Ações
                        render: function(data, type, full) {
                            var btns = '<div class="d-flex gap-2">';
                            btns += '<button class="btn btn-sm btn-icon btn-label-secondary btn-view" data-id="' + data + '" title="{{ __('View') }}"><i class="ti tabler-eye"></i></button>';

                            // Botão WhatsApp sempre visível
                            btns += '<button class="btn btn-sm btn-icon btn-label-info btn-whatsapp" data-id="' + data + '" title="WhatsApp"><i class="ti tabler-brand-whatsapp"></i></button>';

                            if (full.status === 'pending') {
                                btns += '<button class="btn btn-sm btn-icon btn-label-success btn-approve" data-id="' + data + '" title="{{ __('Approve') }}"><i class="ti tabler-check"></i></button>';
                                btns += '<button class="btn btn-sm btn-icon btn-label-danger btn-reject" data-id="' + data + '" title="{{ __('Reject') }}"><i class="ti tabler-x"></i></button>';
                            }
                            btns += '</div>';
                            return btns;
                        }
                    }
                ]
            });

            // Eventos de clique (usando delegação via jQuery para garantir funcionamento em AJAX)
            $(document).on('click', '.view-client', function() {
                var id = $(this).data('id');
                $('#quickViewTitle').text("{{ __('Customer Details') }}");
                $('#quickViewModal').modal('show');
                $('#quickViewContent').html('<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>');
                $.get('/clients/' + id + '/quick-view', function(h) {
                    $('#quickViewContent').html(h);
                });
            });

            $(document).on('click', '.btn-view', function() {
                var id = $(this).data('id');
                $('#quickViewTitle').text("{{ __('Budget Details') }} #" + id);
                $('#quickViewModal').modal('show');
                $('#quickViewContent').html('<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>');
                $.get('/budgets/' + id + '/quick-view', function(h) {
                    $('#quickViewContent').html(h);
                });
            });

            $(document).on('click', '.btn-whatsapp', function() {
                var id = $(this).data('id');
                $.get('/budgets/' + id + '/whatsapp', function(r) {
                    if (r.success) window.open(r.url, '_blank');
                    else alert(r.message);
                });
            });

            $(document).on('click', '.btn-approve', function() {
                var id = $(this).data('id');
                if (confirm("{{ __('Approve and generate OS?') }}")) {
                    $.post('/budgets/' + id + '/convert', {
                        _token: '{{ csrf_token() }}'
                    }, function() {
                        dt.draw();
                    });
                }
            });

            $(document).on('click', '.btn-reject', function() {
                var id = $(this).data('id');

                Swal.fire({
                    title: "{{ __('Reject Budget') }}",
                    text: "{{ __('Reason for rejection:') }}",
                    input: 'textarea',
                    inputPlaceholder: 'Ex: Valor acima do esperado...',
                    inputAttributes: {
                        'aria-label': 'Motivo da rejeição'
                    },
                    showCancelButton: true,
                    confirmButtonText: "{{ __('Confirm Rejection') }}",
                    cancelButtonText: "{{ __('Cancel') }}",
                    customClass: {
                        confirmButton: 'btn btn-danger me-3',
                        cancelButton: 'btn btn-label-secondary'
                    },
                    buttonsStyling: false,
                    inputValidator: (value) => {
                        if (!value) {
                            return "{{ __('You must provide a reason!') }}"
                        }
                    }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.post('/budgets/' + id + '/status', {
                            _token: '{{ csrf_token() }}',
                            status: 'rejected',
                            reason: result.value
                        }, function() {
                            dt.draw();
                            Swal.fire({
                                icon: 'success',
                                title: "{{ __('Rejected!') }}",
                                text: "{{ __('Budget marked as rejected.') }}",
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            });
                        });
                    }
                });
            });
        }
    });
</script>
@endsection