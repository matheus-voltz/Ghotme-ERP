@extends('layouts/layoutMaster')

@section('title', niche('entities'))

@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js'])
@endsection

@section('content')
<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">{{ niche('entities') }}</h5>
        <a href="{{ route('ordens-servico.create') }}" class="btn btn-primary">
            <i class="ti tabler-plus me-1"></i> Nova {{ niche('entity') }}
        </a>
    </div>
    <div class="card-datatable table-responsive p-2">
        <table class="datatables-os table table-hover border-top">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ niche('entity') }}</th>
                    <th>Aberto por</th>
                    <th>{{ __('Status') }}</th>
                    <th>Total (R$)</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dt_table = document.querySelector('.datatables-os');
        if (dt_table) {
            const dt = new DataTable(dt_table, {
                ajax: "{{ route('ordens-servico.data') }}",
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'date'
                    },
                    {
                        data: 'client'
                    },
                    {
                        data: 'vehicle'
                    },
                    {
                        data: 'opened_by'
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
                        targets: 5,
                        render: (data) => {
                            const colors = {
                                pending: 'warning',
                                in_progress: 'primary',
                                finalized: 'success',
                                completed: 'success',
                                running: 'info',
                                canceled: 'danger'
                            };
                            const currentNiche = "{{ get_current_niche() }}";
                            const statusTranslations = {
                                pending: currentNiche === 'food_service' ? 'Pendente' : 'Pendente',
                                in_progress: currentNiche === 'food_service' ? 'Preparando' : 'Em Execução',
                                finalized: 'Finalizada',
                                completed: currentNiche === 'food_service' ? 'Pronto / Entregue' : 'Pronto p/ Retirada',
                                running: currentNiche === 'food_service' ? 'Na Cozinha' : 'Em Andamento',
                                canceled: 'Cancelada',
                                paid: 'Finalizado / Pago',
                                awaiting_approval: currentNiche === 'food_service' ? 'Aguard. Pagto' : 'Aguardando Aprovação'
                            };
                            return `<span class="badge badge-soft bg-label-${colors[data] || 'secondary'}">${statusTranslations[data] || data}</span>`;
                        }
                    },
                    {
                        targets: 6,
                        render: (data) => `R$ ${parseFloat(data).toFixed(2)}`
                    },
                    {
                        targets: 7,
                        render: (data, type, full) => {
                            const isFood = "{{ get_current_niche() }}" === 'food_service';
                            let html = `<div class="d-flex gap-2">`;
                            
                            // Botão Detalhes / Mesa
                            const editTitle = isFood ? 'Ver Itens / Mesa' : "{{ __('Edit') }}";
                            const editIcon = isFood ? 'tabler-tools-kitchen-2' : 'tabler-edit';
                            html += `<a href="/ordens-servico/${data}/edit" class="btn btn-sm btn-primary" title="${editTitle}"><i class="ti ${editIcon}"></i></a>`;
                            
                            // Botão Finalizar / Entregar
                            if (full.status !== 'finalized' && full.status !== 'paid') {
                                const finalizeTitle = isFood ? 'Concluir / Entregar' : "{{ __('Finalize') }}";
                                html += `<button class="btn btn-sm btn-success finalize-os" data-id="${data}" title="${finalizeTitle}"><i class="ti tabler-check"></i></button>`;
                            }

                            // Botão Ficha de Preparo / Imprimir Comanda
                            const ticketTitle = isFood ? 'Imprimir Comanda' : 'Checklist/Vistoria';
                            const ticketIcon = isFood ? 'tabler-printer' : 'tabler-clipboard-check';
                            const ticketUrl = isFood ? `/ordens-servico/${data}/print-order` : `/ordens-servico/checklist/create?os_id=${data}`;
                            html += `<a href="${ticketUrl}" target="_blank" class="btn btn-sm btn-info" title="${ticketTitle}"><i class="ti ${ticketIcon}"></i></a>`;
                            
                            html += `</div>`;
                            return html;
                        }
                    }
                ]
            });

            document.addEventListener('click', function(e) {
                if (e.target.closest('.finalize-os')) {
                    const id = e.target.closest('.finalize-os').dataset.id;
                    fetch(`${baseUrl}ordens-servico/${id}/status`, {
                        method: 'POST',
                        body: JSON.stringify({
                            status: 'finalized'
                        }),
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Content-Type': 'application/json'
                        }
                    }).then(() => dt.ajax.reload());
                }
            });
        }

        // Prompt para impressão de etiqueta se acabou de criar OS
        @if(session('just_created_os'))
        Swal.fire({
            title: '{{ niche("entity") }} Criada com Sucesso!',
            text: 'Deseja gerar a etiqueta com QR Code para este {{ niche("entity") }}?',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Sim, Gerar Etiqueta',
            cancelButtonText: 'Agora Não',
            customClass: {
                confirmButton: 'btn btn-primary me-3',
                cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.open("{{ route('ordens-servico.print-label', session('just_created_os')) }}", "_blank");
            }
        });
        @endif
    });
</script>
@endsection