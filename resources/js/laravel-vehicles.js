/**
 * Vehicles Management Script
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
    const dt_table = document.querySelector('.datatables-vehicles'),
        offCanvasForm = document.getElementById('offcanvasAddVehicles'),
        formVehicle = document.getElementById('addNewVehicleForm');

    // Select2 initialization
    var select2 = $('.select2');
    if (select2.length) {
        select2.wrap('<div class="position-relative"></div>').select2({
            placeholder: 'Selecione o Cliente',
            dropdownParent: select2.parent()
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
        document.addEventListener('click', function(e) {
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
        document.addEventListener('click', function(e) {
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
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-record')) {
                const id = e.target.closest('.edit-record').dataset.id;
                document.getElementById('offcanvasAddVehiclesLabel').innerHTML = 'Editar Veículo';
                fetch(`${baseUrl}vehicles-list/${id}/edit`)
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('vehicle_id').value = data.id;
                        document.getElementById('add-vehicle-placa').value = data.placa;
                        document.getElementById('add-vehicle-marca').value = data.marca;
                        document.getElementById('add-vehicle-modelo').value = data.modelo;
                        document.getElementById('add-vehicle-ano-fabricacao').value = data.ano_fabricacao || '';
                        document.getElementById('add-vehicle-renavam').value = data.renavam || '';
                        $('#vehicle-cliente').val(data.cliente_id).trigger('change');
                        $('#vehicle-status').val(data.ativo ? '1' : '0');
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
            });
        }
    }

    // Submit
    if (formVehicle) {
        formVehicle.addEventListener('submit', function(e) {
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
                    Swal.fire({ icon: 'success', title: 'Sucesso!', text: data.message });
                    location.reload();
                } else {
                    let errorMsg = 'Verifique os dados informados.';
                    if (data.errors) {
                        errorMsg = '<ul class="text-start">';
                        Object.keys(data.errors).forEach(key => {
                            errorMsg += `<li>${data.errors[key][0]}</li>`;
                        });
                        errorMsg += '</ul>';
                    }
                    Swal.fire({ icon: 'error', title: 'Erro de Validação', html: errorMsg });
                }
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Erro!', text: 'Ocorreu um erro ao processar a requisição.' });
            });
        });
    }
});