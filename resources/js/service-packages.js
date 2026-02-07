/**
 * Service Packages management
 */

'use strict';

document.addEventListener('DOMContentLoaded', function (e) {
    const dt_table = document.querySelector('.datatables-packages'),
        offCanvasForm = document.getElementById('offcanvasPackage'),
        formPackage = document.getElementById('formPackage'),
        addPartSelect = $('#add-part-select'),
        partsList = document.getElementById('selected-parts-list');

    // Select2
    $('.select2').each(function () {
        var $this = $(this);
        $this.wrap('<div class="position-relative"></div>').select2({
            dropdownParent: $this.parent(),
            placeholder: $this.attr('placeholder') || 'Selecione'
        });
    });

    if (dt_table) {
        const dt_packages = new DataTable(dt_table, {
            processing: true,
            serverSide: true,
            ajax: { url: baseUrl + 'services/packages-list' },
            columns: [
                { data: 'fake_id' },
                { data: 'name' },
                { data: 'id' }, // Items count
                { data: 'total_price' },
                { data: 'is_active' },
                { data: 'action' }
            ],
            columnDefs: [
                {
                    targets: 1,
                    render: function (data) { return `<span class="fw-medium text-heading">${data}</span>`; }
                },
                {
                    targets: 2,
                    render: function (data, type, full) {
                        return `<small>${full.services_count} serv. / ${full.parts_count} peças</small>`;
                    }
                },
                {
                    targets: 3,
                    render: function (data) { return data ? `R$ ${parseFloat(data).toFixed(2)}` : '<span class="text-muted">Calculado</span>'; }
                },
                {
                    targets: 4,
                    className: 'text-center',
                    render: function (data) { return data ? '<span class="badge bg-label-success">Ativo</span>' : '<span class="badge bg-label-danger">Inativo</span>'; }
                },
                {
                    targets: 5,
                    title: 'Ações',
                    orderable: false,
                    render: function (data, type, full) {
                        return (
                            '<div class="d-flex align-items-center gap-2">' +
                            `<button class="btn btn-sm btn-icon edit-record" data-id="${full.id}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasPackage"><i class="ti tabler-edit"></i></button>` +
                            `<button class="btn btn-sm btn-icon delete-record" data-id="${full.id}"><i class="ti tabler-trash"></i></button>` +
                            '</div>'
                        );
                    }
                }
            ],
            layout: {
                topEnd: {
                    features: [
                        { search: { placeholder: 'Procurar pacote' } },
                        {
                            buttons: [{
                                text: '<i class="ti tabler-plus me-1"></i> Criar Pacote',
                                className: 'add-new btn btn-primary',
                                attr: { 'data-bs-toggle': 'offcanvas', 'data-bs-target': '#offcanvasPackage' }
                            }]
                        }
                    ]
                }
            }
        });

        // Add part to list
        addPartSelect.on('select2:select', function (e) {
            const id = e.params.data.id;
            const name = $(e.params.data.element).data('name');
            if (!id) return;

            addPartRow(id, name, 1);
            addPartSelect.val(null).trigger('change');
        });

        function addPartRow(id, name, qty) {
            if (document.getElementById(`part-row-${id}`)) return;

            const row = document.createElement('div');
            row.id = `part-row-${id}`;
            row.className = 'd-flex align-items-center mb-2 gap-2 border p-2 rounded';
            row.innerHTML = `
                <input type="hidden" name="parts[]" value="${id}">
                <div class="flex-grow-1"><small>${name}</small></div>
                <div style="width: 80px">
                    <input type="number" name="parts_qty[${id}]" class="form-control form-control-sm" value="${qty}" min="1">
                </div>
                <button type="button" class="btn btn-sm btn-icon text-danger remove-part"><i class="ti tabler-x"></i></button>
            `;
            partsList.appendChild(row);
        }

        document.addEventListener('click', function (e) {
            if (e.target.closest('.remove-part')) {
                e.target.closest('.remove-part').parentElement.remove();
            }
        });

        // Edit
        document.addEventListener('click', function (e) {
            if (e.target.closest('.edit-record')) {
                const id = e.target.closest('.edit-record').dataset.id;
                document.getElementById('offcanvasPackageLabel').innerHTML = 'Editar Pacote';
                partsList.innerHTML = '';

                fetch(`${baseUrl}services/packages/${id}/edit`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('package_id').value = data.id;
                        document.getElementById('package-name').value = data.name;
                        document.getElementById('package-total-price').value = data.total_price || '';
                        document.getElementById('package-description').value = data.description || '';

                        const serviceIds = data.services.map(s => s.id);
                        $('#package-services').val(serviceIds).trigger('change');

                        data.parts.forEach(part => {
                            addPartRow(part.id, part.name, part.pivot.quantity);
                        });
                    });
            }
        });

        const addNewBtn = document.querySelector('.add-new');
        if (addNewBtn) {
            addNewBtn.addEventListener('click', function () {
                document.getElementById('package_id').value = '';
                document.getElementById('offcanvasPackageLabel').innerHTML = 'Novo Pacote';
                formPackage.reset();
                $('#package-services').val(null).trigger('change');
                partsList.innerHTML = '';
            });
        }
    }

    // Submit
    if (formPackage) {
        formPackage.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(formPackage);
            const id = formData.get('id');
            const url = id ? `${baseUrl}services/packages/${id}` : `${baseUrl}services/packages`;
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
                            formPackage.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                            formPackage.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                            Object.keys(data.errors).forEach(key => {
                                const input = formPackage.querySelector(`[name="${key}"]`);
                                if (input) {
                                    input.classList.add('is-invalid');
                                    const feedback = document.createElement('div');
                                    feedback.className = 'invalid-feedback';
                                    feedback.innerText = data.errors[key][0];

                                    if (input.nextElementSibling && input.nextElementSibling.classList.contains('select2-container')) {
                                        input.nextElementSibling.after(feedback);
                                    } else {
                                        input.after(feedback);
                                    }
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
