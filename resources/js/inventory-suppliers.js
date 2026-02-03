/**
 * Inventory Suppliers management
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
    let borderColor, bodyBg, headingColor;

    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;

    const dt_suppliers_table = document.querySelector('.datatables-suppliers'),
        offCanvasForm = document.getElementById('offcanvasAddSuppliers');

    // ajax setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Suppliers datatable
    if (dt_suppliers_table) {
        const dt_suppliers = new DataTable(dt_suppliers_table, {
            processing: true,
            serverSide: true,
            ajax: {
                url: baseUrl + 'inventory/suppliers-list',
                dataSrc: function (json) {
                    if (typeof json.recordsTotal !== 'number') json.recordsTotal = 0;
                    if (typeof json.recordsFiltered !== 'number') json.recordsFiltered = 0;
                    json.data = Array.isArray(json.data) ? json.data : [];
                    return json.data;
                }
            },
            columns: [
                { data: 'fake_id' },
                { data: 'id' },
                { data: 'name' },
                { data: 'contact_name' },
                { data: 'email' },
                { data: 'phone' },
                { data: 'city' },
                { data: 'is_active' },
                { data: 'action' }
            ],
            columnDefs: [
                {
                    className: 'control',
                    searchable: false,
                    orderable: false,
                    responsivePriority: 2,
                    targets: 0,
                    render: function (data, type, full, meta) { return ''; }
                },
                {
                    searchable: false,
                    orderable: false,
                    targets: 1,
                    render: function (data, type, full, meta) { return `<span>${full.fake_id}</span>`; }
                },
                {
                    targets: 2,
                    responsivePriority: 4,
                    render: function (data, type, full, meta) {
                        return `<div class="d-flex flex-column"><span class="fw-medium text-heading">${full.name}</span><small class="text-muted">${full.trade_name || ''}</small></div>`;
                    }
                },
                {
                    targets: 3,
                    render: function (data, type, full, meta) { return full.contact_name || '-'; }
                },
                {
                    targets: 4,
                    render: function (data, type, full, meta) { return full.email || '-'; }
                },
                {
                    targets: 5,
                    render: function (data, type, full, meta) { return full.phone || '-'; }
                },
                {
                    targets: 6,
                    render: function (data, type, full, meta) { return full.city || '-'; }
                },
                {
                    targets: 7,
                    className: 'text-center',
                    render: function (data, type, full, meta) {
                        return full.is_active ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>';
                    }
                },
                {
                    targets: 8,
                    title: 'Ações',
                    searchable: false,
                    orderable: false,
                    render: function (data, type, full, meta) {
                         return (
                            '<div class="d-flex align-items-center gap-4">' +
                            `<button class="btn btn-sm btn-icon edit-record" data-id="${full.id}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddSuppliers"><i class="icon-base ti tabler-edit icon-22px"></i></button>` +
                            `<button class="btn btn-sm btn-icon delete-record" data-id="${full.id}"><i class="icon-base ti tabler-trash icon-22px"></i></button>` +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[2, 'asc']],
            layout: {
                topStart: {
                    rowClass: 'row m-3 my-0 justify-content-between',
                    features: [{ pageLength: { menu: [10, 20, 50, 100], text: '_MENU_' } }]
                },
                topEnd: {
                    features: [
                        { search: { placeholder: 'Procurar fornecedor', text: '_INPUT_' } },
                        {
                            buttons: [{
                                text: '<i class="icon-base ti tabler-plus icon-sm me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Adicionar Fornecedor</span>',
                                className: 'add-new btn btn-primary',
                                attr: { 'data-bs-toggle': 'offcanvas', 'data-bs-target': '#offcanvasAddSuppliers' }
                            }]
                        }
                    ]
                },
                bottomStart: {
                    rowClass: 'row mx-3 justify-content-between',
                    features: [{ info: { text: 'Showing _START_ to _END_ of _TOTAL_ entries' } }]
                },
                bottomEnd: 'paging'
            },
            displayLength: 10,
            responsive: {
                details: {
                    display: DataTable.Responsive.display.modal({
                        header: function (row) { return 'Detalhes de ' + row.data().name; }
                    }),
                    type: 'column',
                    renderer: function (api, rowIdx, columns) {
                        const data = columns.map(col => col.title !== '' ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}"><td>${col.title}:</td><td>${col.data}</td></tr>` : '').join('');
                        return data ? `<div class="table-responsive"><table class="table"><tbody>${data}</tbody></table></div>` : false;
                    }
                }
            }
        });

        // Delete Record
        document.addEventListener('click', function (e) {
            if (e.target.closest('.delete-record')) {
                const id = e.target.closest('.delete-record').dataset.id;
                Swal.fire({
                    title: 'Você tem certeza?',
                    text: "Você não poderá reverter isso!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, exclua!',
                    customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
                    buttonsStyling: false
                }).then(function (result) {
                    if (result.value) {
                        fetch(`${baseUrl}inventory/suppliers/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json' }
                        }).then(response => {
                            if (response.ok) {
                                dt_suppliers.draw();
                                Swal.fire({ icon: 'success', title: 'Excluído!', text: 'O fornecedor foi excluído!', customClass: { confirmButton: 'btn btn-success' } });
                            }
                        });
                    }
                });
            }
        });

        // Edit Record
        document.addEventListener('click', function (e) {
            if (e.target.closest('.edit-record')) {
                const id = e.target.closest('.edit-record').dataset.id;
                document.getElementById('offcanvasAddSuppliersLabel').innerHTML = 'Editar Fornecedor';
                
                fetch(`${baseUrl}inventory/suppliers/${id}/edit`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('supplier_id').value = data.id;
                        document.getElementById('add-supplier-name').value = data.name;
                        document.getElementById('add-supplier-trade-name').value = data.trade_name || '';
                        document.getElementById('add-supplier-contact-name').value = data.contact_name || '';
                        document.getElementById('add-supplier-email').value = data.email || '';
                        document.getElementById('add-supplier-phone').value = data.phone || '';
                        document.getElementById('add-supplier-document').value = data.document || '';
                        document.getElementById('add-supplier-city').value = data.city || '';
                        document.getElementById('add-supplier-address').value = data.address || '';
                    });
            }
        });

        // Reset form
        const addNewBtn = document.querySelector('.add-new');
        if (addNewBtn) {
            addNewBtn.addEventListener('click', function () {
                document.getElementById('supplier_id').value = '';
                document.getElementById('offcanvasAddSuppliersLabel').innerHTML = 'Adicionar Fornecedor';
                document.getElementById('addNewSuppliersForm').reset();
            });
        }
    }

    // Form Validation
    const addNewSuppliersForm = document.getElementById('addNewSuppliersForm');
    if (addNewSuppliersForm) {
        const fv = FormValidation.formValidation(addNewSuppliersForm, {
            fields: {
                name: { validators: { notEmpty: { message: 'Por favor preencha a Razão Social' } } },
                email: { validators: { emailAddress: { message: 'O email não é válido' } } }
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: '', rowSelector: '.mb-6' }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus()
            }
        }).on('core.form.valid', function () {
            const formData = new FormData(addNewSuppliersForm);
            const formDataObj = {};
            formData.forEach((value, key) => formDataObj[key] = value);
            
            const id = formDataObj['id'];
            const url = id ? `${baseUrl}inventory/suppliers/${id}` : `${baseUrl}inventory/suppliers`;
            const method = id ? 'PUT' : 'POST';

            const searchParams = new URLSearchParams();
            for (const [key, value] of Object.entries(formDataObj)) {
                searchParams.append(key, value);
            }

            fetch(url, {
                method: method,
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/x-www-form-urlencoded' },
                body: searchParams.toString()
            }).then(response => {
                if (!response.ok) throw new Error('Error');
                return response.json();
            }).then(data => {
                dt_suppliers_table && new DataTable(dt_suppliers_table).draw();
                bootstrap.Offcanvas.getInstance(offCanvasForm).hide();
                Swal.fire({ icon: 'success', title: 'Sucesso!', text: data.message, customClass: { confirmButton: 'btn btn-success' } });
            });
        });
    }
});
