/**
 * Vehicles Management Script
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
    const dt_table = document.querySelector('.datatables-vehicles'),
        offCanvasForm = document.getElementById('offcanvasAddVehicles'),
        formVehicle = document.getElementById('addNewVehicleForm'),
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

    // Select2 initialization
    var select2 = $('.select2');
    if (select2.length) {
        select2.wrap('<div class="position-relative"></div>').select2({
            placeholder: 'Selecione o Cliente',
            dropdownParent: select2.parent()
        });
    }

    // License Plate Lookup
    const placaInput = document.getElementById('add-vehicle-placa');
    if (placaInput) {
        placaInput.addEventListener('blur', function () {
            const placa = this.value.replace(/[^a-zA-Z0-9]/g, '');
            if (placa.length >= 7) {
                // Show loading state
                const originalPlaceholder = this.placeholder;
                this.placeholder = 'Buscando...';
                
                fetch(`${baseUrl}api/vehicle-lookup/${placa}`)
                    .then(res => res.json())
                    .then(data => {
                        this.placeholder = originalPlaceholder;
                        if (!data.error && !data.message) {
                            // Auto-fill fields if data is returned
                            if (data.marca) document.getElementById('add-vehicle-marca').value = data.marca;
                            if (data.modelo) document.getElementById('add-vehicle-modelo').value = data.modelo;
                            if (data.ano) document.getElementById('add-vehicle-ano-fabricacao').value = data.ano;
                            
                            // Optional: Toast notification
                            // Swal.fire({ icon: 'success', title: 'Veículo encontrado!', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                        }
                    })
                    .catch(() => {
                        this.placeholder = originalPlaceholder;
                    });
            }
        });
    }

    if (dt_table) {
        const dt_vehicles = new DataTable(dt_table, {
            processing: true,
            serverSide: true,
            ajax: { url: baseUrl + 'vehicles-list' },
            columns: [
                { data: 'id' },
                { data: 'id' },
                { data: 'placa' },
                { data: 'marca' },
                { data: 'modelo' },
                { data: 'ano_fabricacao' },
                { data: 'ativo' },
                { data: 'id' }
            ],
            columnDefs: [
                {
                    className: 'control', orderable: false, targets: 0, render: () => ''
                },
                {
                    targets: 1, render: (data, type, full) => `<span>${full.fake_id}</span>`
                },
                {
                    targets: 2,
                    render: (data, type, full) => `<a href="javascript:void(0)" class="fw-bold text-primary view-dossier" data-id="${full.id}">${data}</a>`
                },
                {
                    targets: 6,
                    render: (data) => `<span class="badge bg-label-${data ? 'success' : 'danger'}">${data ? 'Sim' : 'Não'}</span>`
                },
                {
                    targets: -1,
                    title: 'Ações',
                    orderable: false,
                    render: (data, type, full) => {
                        return (
                            '<div class="d-flex align-items-center gap-2">' +
                            `<button class="btn btn-sm btn-icon edit-record" data-id="${full.id}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddVehicles"><i class="ti tabler-edit"></i></button>` +
                            `<button class="btn btn-sm btn-icon delete-record" data-id="${full.id}"><i class="ti tabler-trash"></i></button>` +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[1, 'desc']]
        });

        // Dossier Modal
        document.addEventListener('click', function (e) {
            if (e.target.closest('.view-dossier')) {
                const id = e.target.closest('.view-dossier').dataset.id;
                $('#viewDossierModal').modal('show');
                $('#dossierModalContent').html('<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>');

                fetch(`${baseUrl}vehicles/${id}/dossier`)
                    .then(res => res.text())
                    .then(html => {
                        $('#dossierModalContent').html(html);
                    });
            }
        });

        // Delete
        document.addEventListener('click', function (e) {
            if (e.target.closest('.delete-record')) {
                const id = e.target.closest('.delete-record').dataset.id;
                Swal.fire({
                    title: 'Remover veículo?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, excluir',
                    customClass: { confirmButton: 'btn btn-danger me-3', cancelButton: 'btn btn-label-secondary' },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch(`${baseUrl}vehicles-list/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                        }).then(() => dt_vehicles.ajax.reload());
                    }
                });
            }
        });

        // Edit
        document.addEventListener('click', function (e) {
            if (e.target.closest('.edit-record')) {
                const id = e.target.closest('.edit-record').dataset.id;
                document.getElementById('offcanvasAddVehiclesLabel').innerHTML = 'Editar Veículo';
                fetch(`${baseUrl}vehicles-list/${id}/edit`)
                    .then(res => res.json())
                    .then(data => {
                        const vehicle = data.vehicle;
                        document.getElementById('vehicle_id').value = vehicle.id;
                        document.getElementById('add-vehicle-placa').value = vehicle.placa;
                        document.getElementById('add-vehicle-marca').value = vehicle.marca;
                        document.getElementById('add-vehicle-modelo').value = vehicle.modelo;
                        document.getElementById('add-vehicle-ano-fabricacao').value = vehicle.ano_fabricacao || '';
                        document.getElementById('add-vehicle-renavam').value = vehicle.renavam || '';
                        $('#vehicle-cliente').val(vehicle.cliente_id).trigger('change');
                        $('#vehicle-status').val(vehicle.ativo ? '1' : '0');

                        // Renderizar campos personalizados
                        renderCustomFields(data.custom_fields);
                    });
            }
        });

        const addNewBtn = document.querySelector('.add-new');
        if (addNewBtn) {
            addNewBtn.addEventListener('click', () => {
                document.getElementById('vehicle_id').value = '';
                formVehicle.reset();
                $('#vehicle-cliente').val('').trigger('change');
                document.getElementById('offcanvasAddVehiclesLabel').innerHTML = 'Cadastrar Veículo';

                // Carregar campos personalizados vazios para novo veículo
                fetch(`${baseUrl}settings/custom-fields?entity=Vehicles`)
                    .then(res => res.json())
                    .then(fields => {
                        renderCustomFields(fields);
                    }).catch(() => {
                        customFieldsContainer.classList.add('d-none');
                    });
            });
        }
    }

    // Submit
    if (formVehicle) {
        formVehicle.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(formVehicle);

            fetch(`${baseUrl}vehicles-list`, {
                method: 'POST',
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
                            customClass: { confirmButton: 'btn btn-primary' },
                            buttonsStyling: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        // Limpar erros anteriores
                        formVehicle.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                        formVehicle.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                        if (data.errors) {
                            Object.keys(data.errors).forEach(key => {
                                const input = formVehicle.querySelector(`[name="${key}"]`);
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
                                        // Para Select2
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
                            Swal.fire({ icon: 'error', title: 'Erro!', text: data.message || 'Erro inesperado' });
                        }
                    }
                })
                .catch(err => {
                    Swal.fire({ icon: 'error', title: 'Erro!', text: 'Ocorreu um erro ao processar a requisição.' });
                });
        });
    }
});