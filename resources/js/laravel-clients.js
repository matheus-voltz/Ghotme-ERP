/**
 * Professional Clients Management
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
    const dt_table = document.querySelector('.datatables-clients'),
        offCanvasForm = document.getElementById('offcanvasAddClients'),
        formClient = document.getElementById('addNewClientsForm'),
        sectionPF = document.getElementById('sectionPF'),
        sectionPJ = document.getElementById('sectionPJ');

    // Toggle PF/PJ
    document.querySelectorAll('.client-type-toggle').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'PF') {
                sectionPF.classList.remove('d-none');
                sectionPJ.classList.add('d-none');
            } else {
                sectionPF.classList.add('d-none'); sectionPJ.classList.remove('d-none');
            }
        });
    });

    if (dt_table) {
        const dt_clients = new DataTable(dt_table, {
            processing: true,
            serverSide: true,
            ajax: { url: baseUrl + 'clients-list' },
            columns: [
                { data: 'id' },
                { data: 'id' },
                { data: 'type' },
                { data: 'name' },
                { data: 'document' },
                { data: 'email' },
                { data: 'vehicles_count' },
                { data: 'action' }
            ],
            columnDefs: [
                {
                    className: 'control',
                    orderable: false,
                    targets: 0,
                    render: () => ''
                },
                {
                    targets: 2,
                    render: (data) => `<span class="badge bg-label-${data === 'PF' ? 'success' : 'info'}">${data}</span>`
                },
                {
                    targets: 3,
                    render: (data, type, full) => {
                        return `
                            <div class="d-flex flex-column">
                                <span class="fw-medium text-heading">${data}</span>
                                <small class="text-muted">${full.whatsapp || '-'}</small>
                            </div>
                        `;
                    }
                },
                {
                    targets: 6,
                    render: (data) => `<span class="badge rounded-pill bg-label-primary">${data} veículos</span>`
                },
                {
                    targets: 7,
                    title: 'Ações',
                    orderable: false,
                    render: (data, type, full) => {
                        return (
                            '<div class="d-flex align-items-center gap-2">' +
                            `<button class="btn btn-sm btn-icon edit-record" data-id="${full.id}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddClients"><i class="ti tabler-edit"></i></button>` +
                            `<button class="btn btn-sm btn-icon delete-record" data-id="${full.id}"><i class="ti tabler-trash"></i></button>` +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[1, 'desc']],
            layout: {
                topEnd: {
                    features: [{ search: { placeholder: 'Buscar cliente...' } }]
                }
            }
        });

        // Delete
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-record')) {
                const id = e.target.closest('.delete-record').dataset.id;
                Swal.fire({
                    title: 'Excluir cliente?',
                    text: "Isso removerá também o vínculo com veículos!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, excluir',
                    customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
                    buttonsStyling: false
                }).then(result => {
                    if (result.value) {
                        fetch(`${baseUrl}clients-list/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                        }).then(() => dt_clients.draw());
                    }
                });
            }
        });

        // Edit
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-record')) {
                const id = e.target.closest('.edit-record').dataset.id;
                document.getElementById('offcanvasAddClientsLabel').innerHTML = 'Editar Cliente';
                fetch(`${baseUrl}clients-list/${id}/edit`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('client_id').value = data.id;
                        if (data.type === 'PF') {
                            document.getElementById('typePF').checked = true;
                            sectionPF.classList.remove('d-none'); sectionPJ.classList.add('d-none');
                            document.querySelector('[name="name"]').value = data.name;
                            document.querySelector('[name="cpf"]').value = data.cpf;
                        } else {
                            document.getElementById('typePJ').checked = true;
                            sectionPF.classList.add('d-none'); sectionPJ.classList.remove('d-none');
                            document.querySelector('[name="company_name"]').value = data.company_name;
                            document.querySelector('[name="cnpj"]').value = data.cnpj;
                        }
                        document.querySelector('[name="email"]').value = data.email || '';
                        document.querySelector('[name="whatsapp"]').value = data.whatsapp || '';
                        document.querySelector('[name="cep"]').value = data.cep || '';
                        document.querySelector('[name="rua"]').value = data.rua || '';
                        document.querySelector('[name="numero"]').value = data.numero || '';
                        document.querySelector('[name="bairro"]').value = data.bairro || '';
                        document.querySelector('[name="cidade"]').value = data.cidade || '';
                        document.querySelector('[name="estado"]').value = data.estado || '';
                    });
            }
        });

        const addNewBtn = document.querySelector('.add-new');
        if (addNewBtn) {
            addNewBtn.addEventListener('click', () => {
                document.getElementById('client_id').value = '';
                document.getElementById('offcanvasAddClientsLabel').innerHTML = 'Cadastrar Cliente';
                formClient.reset();
                document.getElementById('typePF').checked = true;
                sectionPF.classList.remove('d-none'); sectionPJ.classList.add('d-none');
            });
        }
    }

    // Submit
    if (formClient) {
        formClient.addEventListener('submit', function(e) {
            e.preventDefault();
            const id = document.getElementById('client_id').value;
            const url = id ? `${baseUrl}clients-list/${id}` : `${baseUrl}clients-list`;
            const method = id ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                body: new URLSearchParams(new FormData(formClient)),
                headers: { 
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    bootstrap.Offcanvas.getInstance(offCanvasForm).hide();
                    location.reload(); // Reload to simplify for now
                }
            });
        });
    }
});