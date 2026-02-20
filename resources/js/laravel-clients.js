/**
 * Professional Clients Management with Vehicle support
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
    const dt_table = document.querySelector('.datatables-clients'),
        offCanvasForm = document.getElementById('offcanvasAddClients'),
        formClient = document.getElementById('addNewClientsForm'),
        sectionPF = document.getElementById('sectionPF'),
        sectionPJ = document.getElementById('sectionPJ'),
        customFieldsContainer = document.getElementById('custom-fields-container'),
        customFieldsList = document.getElementById('custom-fields-list');

    // Function to render custom fields
    function renderCustomFields(fields) {
        if (!fields || fields.length === 0) {
            customFieldsContainer.classList.add('d-none');
            return;
        }

        customFieldsContainer.classList.remove('d-none');
        customFieldsList.innerHTML = fields.map(field => {
            const value = field.current_value || '';
            const required = field.required ? 'required' : '';
            let input = '';

            if (field.type === 'select') {
                const options = Array.isArray(field.options) ? field.options : [];
                input = `
                    <select name="custom_fields[${field.id}]" class="form-select" ${required}>
                        <option value="">Selecione...</option>
                        ${options.map(opt => `<option value="${opt}" ${value == opt ? 'selected' : ''}>${opt}</option>`).join('')}
                    </select>`;
            } else if (field.type === 'textarea') {
                input = `<textarea name="custom_fields[${field.id}]" class="form-control" rows="2" ${required}>${value}</textarea>`;
            } else {
                input = `<input type="${field.type}" name="custom_fields[${field.id}]" class="form-control" value="${value}" ${required} />`;
            }

            return `
                <div class="col-md-12 mb-3">
                    <label class="form-label">${field.name}</label>
                    ${input}
                </div>
            `;
        }).join('');
    }

    // Toggle PF/PJ
    document.querySelectorAll('.client-type-toggle').forEach(radio => {
        radio.addEventListener('change', function () {
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
                        const portalLink = `${baseUrl}portal/${full.uuid}`;
                        return (
                            '<div class="d-flex align-items-center gap-2">' +
                            whatsappBtn +
                            `<button class="btn btn-sm btn-icon copy-portal" data-link="${portalLink}" title="Copiar Link do Portal"><i class="ti tabler-link"></i></button>` +
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

        // Copy Portal Link
        document.addEventListener('click', function (e) {
            if (e.target.closest('.copy-portal')) {
                const link = e.target.closest('.copy-portal').dataset.link;
                navigator.clipboard.writeText(link).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Link Copiado!',
                        text: 'O link do portal do cliente foi copiado para a área de transferência.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            }
        });

        // View Record Modal
        document.addEventListener('click', function (e) {
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

        // View Dossier from Client Edit
        document.addEventListener('click', function (e) {
            if (e.target.closest('.view-vehicle-dossier')) {
                const id = e.target.closest('.view-vehicle-dossier').dataset.id;

                // Precisamos garantir que o modal exista na página. 
                // Se não existir (estamos na página de clientes, não veículos), precisamos criar ou redirecionar.
                // Como o modal é grande, o ideal é ter ele na página.
                // Vou injetar o modal dinamicamente se não existir.

                let modalEl = document.getElementById('viewDossierModal');
                if (!modalEl) {
                    const modalHtml = `
                        <div class="modal fade" id="viewDossierModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
                          <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title">Dossiê do Veículo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                              </div>
                              <div class="modal-body" id="dossierModalContent"></div>
                            </div>
                          </div>
                        </div>`;
                    document.body.insertAdjacentHTML('beforeend', modalHtml);
                    modalEl = document.getElementById('viewDossierModal');
                }

                const modal = new bootstrap.Modal(modalEl);
                modal.show();

                document.getElementById('dossierModalContent').innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>';

                fetch(`${baseUrl}vehicles/${id}/dossier`)
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('dossierModalContent').innerHTML = html;
                    });
            }
        });

        // Delete
        document.addEventListener('click', function (e) {
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
        document.addEventListener('click', function (e) {
            if (e.target.closest('.edit-record')) {
                const id = e.target.closest('.edit-record').dataset.id;
                document.getElementById('offcanvasAddClientsLabel').innerHTML = 'Editar Cliente';
                formClient.reset();

                fetch(`${baseUrl}clients-list/${id}/edit`)
                    .then(res => res.json())
                    .then(data => {
                        const client = data.client;
                        document.getElementById('client_id').value = client.id;
                        if (client.type === 'PF') {
                            document.getElementById('typePF').checked = true;
                            sectionPF.classList.remove('d-none'); sectionPJ.classList.add('d-none');
                            document.querySelector('[name="name"]').value = client.name || '';
                            document.querySelector('[name="cpf"]').value = client.cpf || '';
                        } else {
                            document.getElementById('typePJ').checked = true;
                            sectionPF.classList.add('d-none'); sectionPJ.classList.remove('d-none');
                            document.querySelector('[name="company_name"]').value = client.company_name || '';
                            document.querySelector('[name="cnpj"]').value = client.cnpj || '';
                        }
                        document.querySelector('[name="email"]').value = client.email || '';
                        document.querySelector('[name="whatsapp"]').value = client.whatsapp || '';
                        document.querySelector('[name="cep"]').value = client.cep || '';
                        document.querySelector('[name="rua"]').value = client.rua || '';
                        document.querySelector('[name="numero"]').value = client.numero || '';
                        document.querySelector('[name="bairro"]').value = client.bairro || '';
                        document.querySelector('[name="cidade"]').value = client.cidade || '';
                        document.querySelector('[name="estado"]').value = client.estado || '';

                        // Renderizar campos personalizados
                        renderCustomFields(data.custom_fields);

                        // Mostrar veículos existentes
                        const existingSection = document.getElementById('existingVehiclesSection');
                        const listContainer = document.getElementById('existingVehiclesList');
                        const vehicleFields = document.getElementById('vehicleFieldsContainer');
                        const btnToggle = document.getElementById('btnToggleVehicle');

                        if (client.vehicles && client.vehicles.length > 0) {
                            existingSection.classList.remove('d-none');
                            listContainer.innerHTML = client.vehicles.map(v => `
                                <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                    <div>
                                        <span class="fw-bold text-primary">${v.placa}</span> - 
                                        <small>${v.marca} ${v.modelo}</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-label-info btn-icon view-vehicle-dossier" data-id="${v.id}"><i class="ti tabler-eye"></i></button>
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
        document.getElementById('btnToggleVehicle').addEventListener('click', function () {
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

                // Carregar campos personalizados vazios para novo cliente
                // Para isso, precisamos de uma rota que retorne apenas as definições
                // Vou usar a mesma rota de edit mas com ID 0 ou um endpoint específico
                // Como o trait já lida com isso, vou buscar via fetch
                fetch(`${baseUrl}settings/custom-fields?entity=Clients`)
                    .then(res => res.json())
                    .then(fields => {
                        renderCustomFields(fields);
                    }).catch(() => {
                        // Se falhar ou não quiser criar endpoint agora, apenas limpa
                        customFieldsContainer.classList.add('d-none');
                    });

                // No cadastro novo, sempre mostra os campos do veículo
                document.getElementById('existingVehiclesSection').classList.add('d-none');
                document.getElementById('vehicleFieldsContainer').classList.remove('d-none');
                document.getElementById('btnToggleVehicle').classList.add('d-none');
            });
        }
    }

    // Submit
    if (formClient) {
        formClient.addEventListener('submit', function (e) {
            e.preventDefault();
            const id = document.getElementById('client_id').value;
            const url = id ? `${baseUrl}clients-list/${id}` : `${baseUrl}clients-list`;
            const method = id ? 'PUT' : 'POST';

            // Use FormData directly for reliable field capturing
            const formData = new FormData(formClient);

            // Garantir que CPF/CNPJ vão limpos para o servidor para bater com a validação
            if (formData.get('cpf')) formData.set('cpf', formData.get('cpf').replace(/\D/g, ''));
            if (formData.get('cnpj')) formData.set('cnpj', formData.get('cnpj').replace(/\D/g, ''));

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
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: data.message,
                            customClass: { confirmButton: 'btn btn-primary' }
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        // Limpar erros anteriores
                        formClient.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                        formClient.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                        if (data.errors) {
                            Object.keys(data.errors).forEach(key => {
                                const input = formClient.querySelector(`[name="${key}"]`);
                                if (input) {
                                    input.classList.add('is-invalid');

                                    // Criar div de erro
                                    const feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    feedback.innerText = data.errors[key][0];

                                    // Inserir após o input (ou após o container do input se for um grupo)
                                    if (input.closest('.input-group')) {
                                        input.closest('.input-group').after(feedback);
                                    } else if (input.classList.contains('select2-hidden-accessible')) {
                                        const select2Container = input.nextElementSibling;
                                        if (select2Container && select2Container.classList.contains('select2-container')) {
                                            select2Container.after(feedback);
                                        }
                                    } else {
                                        input.after(feedback);
                                    }
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro!',
                                text: data.message || 'Erro inesperado',
                                customClass: { confirmButton: 'btn btn-primary' }
                            });
                        }
                    }
                })
                .catch(err => {
                    Swal.fire({ icon: 'error', title: 'Erro!', text: 'Ocorreu um erro ao processar a requisição.' });
                });
        });
    }
});
