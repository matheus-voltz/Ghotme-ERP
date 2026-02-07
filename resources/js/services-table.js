/**
 * Services Table management
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
    const dt_table = document.querySelector('.datatables-services'),
        offCanvasForm = document.getElementById('offcanvasService'),
        formService = document.getElementById('formService');

    if (dt_table) {
        const dt_services = new DataTable(dt_table, {
            processing: true,
            serverSide: true,
            ajax: {
                url: baseUrl + 'services/list'
            },
            columns: [
                { data: 'fake_id' },
                { data: 'name' },
                { data: 'price' },
                { data: 'estimated_time' },
                { data: 'is_active' },
                { data: 'action' }
            ],
            columnDefs: [
                {
                    targets: 1,
                    render: function (data, type, full) {
                        return `<span class="fw-medium text-heading">${data}</span>`;
                    }
                },
                {
                    targets: 2,
                    render: function (data) {
                        return `R$ ${parseFloat(data).toFixed(2)}`;
                    }
                },
                {
                    targets: 3,
                    render: function (data) {
                        return data ? `${data} min` : '-';
                    }
                },
                {
                    targets: 4,
                    className: 'text-center',
                    render: function (data) {
                        return data ? '<span class="badge bg-label-success">Ativo</span>' : '<span class="badge bg-label-danger">Inativo</span>';
                    }
                },
                {
                    targets: 5,
                    title: 'Ações',
                    orderable: false,
                    render: function (data, type, full) {
                        return (
                            '<div class="d-flex align-items-center gap-2">' +
                            `<button class="btn btn-sm btn-icon edit-record" data-id="${full.id}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasService"><i class="ti tabler-edit"></i></button>` +
                            `<button class="btn btn-sm btn-icon delete-record" data-id="${full.id}"><i class="ti tabler-trash"></i></button>` +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[1, 'asc']],
            layout: {
                topEnd: {
                    features: [
                        { search: { placeholder: 'Procurar serviço' } },
                        {
                            buttons: [{
                                text: '<i class="ti tabler-plus me-1"></i> Adicionar Serviço',
                                className: 'add-new btn btn-primary',
                                attr: { 'data-bs-toggle': 'offcanvas', 'data-bs-target': '#offcanvasService' }
                            }]
                        }
                    ]
                }
            }
        });

        // Delete
        document.addEventListener('click', function (e) {
            if (e.target.closest('.delete-record')) {
                const id = e.target.closest('.delete-record').dataset.id;
                Swal.fire({
                    title: 'Confirmar exclusão?',
                    text: "Esta ação não pode ser revertida!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, excluir!',
                    customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
                    buttonsStyling: false
                }).then(function (result) {
                    if (result.value) {
                        fetch(`${baseUrl}services/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                        }).then(response => response.json()).then(data => {
                            if (data.success) {
                                dt_services.draw();
                                Swal.fire({ icon: 'success', title: 'Excluído!', text: data.message, customClass: { confirmButton: 'btn btn-success' } });
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
                document.getElementById('offcanvasServiceLabel').innerHTML = 'Editar Serviço';
                fetch(`${baseUrl}services/${id}/edit`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('service_id').value = data.id;
                        document.getElementById('service-name').value = data.name;
                        document.getElementById('service-price').value = data.price;
                        document.getElementById('service-time').value = data.estimated_time;
                        document.getElementById('service-description').value = data.description || '';
                    });
            }
        });

        // Reset form
        const addNewBtn = document.querySelector('.add-new');
        if (addNewBtn) {
            addNewBtn.addEventListener('click', function () {
                document.getElementById('service_id').value = '';
                document.getElementById('offcanvasServiceLabel').innerHTML = 'Adicionar Serviço';
                formService.reset();
            });
        }
    }

    // Form Validation & Submit
    if (formService) {
        const fv = FormValidation.formValidation(formService, {
            fields: {
                name: { validators: { notEmpty: { message: 'Nome é obrigatório' } } },
                price: { validators: { notEmpty: { message: 'Preço é obrigatório' } } }
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: '', rowSelector: '.mb-6' }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus()
            }
        }).on('core.form.valid', function () {
            const formData = new FormData(formService);
            const id = formData.get('id');
            const url = id ? `${baseUrl}services/${id}` : `${baseUrl}services`;
            const method = id ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                body: new URLSearchParams(formData),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            formService.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                            formService.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                            Object.keys(data.errors).forEach(key => {
                                const input = formService.querySelector(`[name="${key}"]`);
                                if (input) {
                                    input.classList.add('is-invalid');
                                    const feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    feedback.innerText = data.errors[key][0];
                                    input.after(feedback);
                                }
                            });
                            return;
                        }
                        throw new Error(data.message || 'Erro inesperado');
                    }

                    bootstrap.Offcanvas.getInstance(offCanvasForm).hide();
                    new DataTable(dt_table).draw();
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: data.message,
                        customClass: { confirmButton: 'btn btn-success' }
                    });
                })
                .catch(err => {
                    if (err.message) {
                        Swal.fire({ icon: 'error', title: 'Erro!', text: err.message, customClass: { confirmButton: 'btn btn-primary' } });
                    }
                });
        });
    }
});
