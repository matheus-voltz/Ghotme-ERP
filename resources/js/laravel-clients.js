/**
 * Professional Clients Management with Vehicle support
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
                    className: 'control', orderable: false, targets: 0, render: () => ''
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
                        const whatsappLink = full.whatsapp ? `https://api.whatsapp.com/send?phone=55${full.whatsapp.replace(/\D/g, '')}` : '#';
                        const whatsappBtn = full.whatsapp 
                            ? `<a href="${whatsappLink}" target="_blank" class="btn btn-sm btn-icon text-success" title="WhatsApp"><i class="ti tabler-brand-whatsapp"></i></a>` 
                            : '';
                        return (
                            '<div class="d-flex align-items-center gap-2">' +
                            whatsappBtn +
                            `<button class="btn btn-sm btn-icon view-record" data-id="${full.id}"><i class="ti tabler-eye"></i></button>` +
                            `<button class="btn btn-sm btn-icon edit-record" data-id="${full.id}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddClients" title="Editar"><i class="ti tabler-edit"></i></button>` +
                            `<button class="btn btn-sm btn-icon delete-record" data-id="${full.id}" title="Excluir"><i class="ti tabler-trash"></i></button>` +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[1, 'desc']]
        });

        // View Record Modal
        document.addEventListener('click', function(e) {
            if (e.target.closest('.view-record')) {
                const id = e.target.closest('.view-record').dataset.id;
                $('#viewClientModal').modal('show');
                $('#clientModalContent').html('<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>');
                
                fetch(`${baseUrl}clients/${id}/quick-view`)
                    .then(res => res.text())
                    .then(html => {
                        $('#clientModalContent').html(html);
                    });
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
                    customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch(`${baseUrl}clients-list/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                        }).then(res => res.json()).then(data => {
                            if (data.success) {
                                Swal.fire({ icon: 'success', title: 'Excluído!', text: data.message, customClass: { confirmButton: 'btn btn-success' } });
                                dt_clients.ajax.reload();
                            }
                        });
                    }
                });
            }
        });

        // Edit
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-record')) {
                const id = e.target.closest('.edit-record').dataset.id;
                document.getElementById('offcanvasAddClientsLabel').innerHTML = 'Editar Cliente';
                formClient.reset(); 
                
                fetch(`${baseUrl}clients-list/${id}/edit`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('client_id').value = data.id;
                        if (data.type === 'PF') {
                            document.getElementById('typePF').checked = true;
                            sectionPF.classList.remove('d-none'); sectionPJ.classList.add('d-none');
                            document.querySelector('[name="name"]').value = data.name || '';
                            document.querySelector('[name="cpf"]').value = data.cpf || '';
                        } else {
                            document.getElementById('typePJ').checked = true;
                            sectionPF.classList.add('d-none'); sectionPJ.classList.remove('d-none');
                            document.querySelector('[name="company_name"]').value = data.company_name || '';
                            document.querySelector('[name="cnpj"]').value = data.cnpj || '';
                        }
                        document.querySelector('[name="email"]').value = data.email || '';
                        document.querySelector('[name="whatsapp"]').value = data.whatsapp || '';
                        document.querySelector('[name="cep"]').value = data.cep || '';
                        document.querySelector('[name="rua"]').value = data.rua || '';
                        document.querySelector('[name="numero"]').value = data.numero || '';
                        document.querySelector('[name="bairro"]').value = data.bairro || '';
                        document.querySelector('[name="cidade"]').value = data.cidade || '';
                        document.querySelector('[name="estado"]').value = data.estado || '';
                        
                        // Mostrar veículos existentes
                        const existingSection = document.getElementById('existingVehiclesSection');
                        const listContainer = document.getElementById('existingVehiclesList');
                        const vehicleFields = document.getElementById('vehicleFieldsContainer');
                        const btnToggle = document.getElementById('btnToggleVehicle');
                        
                        if (data.vehicles && data.vehicles.length > 0) {
                            existingSection.classList.remove('d-none');
                            listContainer.innerHTML = data.vehicles.map(v => `
                                <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <div>
                                        <span class="fw-bold text-primary">${v.placa}</span> - 
                                        <small>${v.marca} ${v.modelo}</small>
                                    </div>
                                    <a href="/vehicles" class="btn btn-sm btn-label-secondary btn-icon"><i class="ti tabler-external-link"></i></a>
                                </div>
                            `).join('');
                            
                            // Na edição, se já tem carro, esconde o form de "novo carro" por padrão
                            vehicleFields.classList.add('d-none');
                            btnToggle.classList.remove('d-none');
                        } else {
                            existingSection.classList.add('d-none');
                            vehicleFields.classList.remove('d-none');
                            btnToggle.classList.add('d-none');
                        }
                    });
            }
        });

        // Toggle Vehicle Fields logic
        document.getElementById('btnToggleVehicle').addEventListener('click', function() {
            const container = document.getElementById('vehicleFieldsContainer');
            if (container.classList.contains('d-none')) {
                container.classList.remove('d-none');
                this.innerHTML = '<i class="ti tabler-minus me-1"></i> Ocultar Adição';
            } else {
                container.classList.add('d-none');
                this.innerHTML = '<i class="ti tabler-plus me-1"></i> Adicionar Mais';
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
                
                // No cadastro novo, sempre mostra os campos do veículo
                document.getElementById('existingVehiclesSection').classList.add('d-none');
                document.getElementById('vehicleFieldsContainer').classList.remove('d-none');
                document.getElementById('btnToggleVehicle').classList.add('d-none');
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

            // Use FormData directly for reliable field capturing
            const formData = new FormData(formClient);
            if (id) formData.append('_method', 'PUT');

            fetch(url, {
                method: 'POST', // Always POST for Laravel when sending files/formdata, with _method override
                body: formData,
                headers: { 
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            })
            .then(async res => {
                const data = await res.json();
                if (res.ok && data.success) {
                    bootstrap.Offcanvas.getInstance(offCanvasForm).hide();
                    Swal.fire({ icon: 'success', title: 'Sucesso!', text: data.message });
                    location.reload();
                } else {
                    // Tratar erros de validação de forma detalhada
                    let errorMsg = '<ul class="text-start">';
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            errorMsg += `<li><strong>${key.replace('veiculo_', 'Veículo ').replace('_', ' ')}:</strong> ${data.errors[key][0]}</li>`;
                        });
                    } else {
                        errorMsg += `<li>${data.message || 'Erro desconhecido'}</li>`;
                    }
                    errorMsg += '</ul>';
                    
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Erro de Validação', 
                        html: errorMsg,
                        customClass: { confirmButton: 'btn btn-primary' }
                    });
                }
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Erro!', text: 'Ocorreu um erro ao processar a requisição.' });
            });
        });
    }
});
