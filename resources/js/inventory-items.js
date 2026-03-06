/**
 * Inventory Items management
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
    let borderColor, bodyBg, headingColor;

    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;

    // Variable declaration for table
    const dt_items_table = document.querySelector('.datatables-items'),
        offCanvasForm = document.getElementById('offcanvasAddItems'),
        t = window.inventoryTranslations || {},
        uploadedAvatar = document.getElementById('uploadedAvatar');

    // Select2 initialization
    var select2 = $('.select2');
    if (select2.length) {
        var $this = select2;
        $this.wrap('<div class="position-relative"></div>').select2({
            placeholder: t['Select'] || 'Selecione',
            dropdownParent: $this.parent()
        });
    }

    // ajax setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Items datatable
    if (dt_items_table) {
        const dt_items = new DataTable(dt_items_table, {
            processing: true,
            serverSide: true,
            ajax: {
                url: baseUrl + 'inventory/items-list',
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
                { data: 'sku' },
                { data: 'quantity' },
                { data: 'selling_price' },
                { data: 'location' },
                { data: 'is_active' },
                { data: 'action' }
            ],
            columnDefs: [
                {
                    // For Responsive
                    className: 'control',
                    searchable: false,
                    orderable: false,
                    responsivePriority: 2,
                    targets: 0,
                    render: function (data, type, full, meta) {
                        return '';
                    }
                },
                {
                    searchable: false,
                    orderable: false,
                    targets: 1,
                    render: function (data, type, full, meta) {
                        return `<span>${full.fake_id}</span>`;
                    }
                },
                {
                    // Name
                    targets: 2,
                    responsivePriority: 4,
                    render: function (data, type, full, meta) {
                        return `<span class="fw-medium">${full.name}</span>`;
                    }
                },
                {
                    // SKU
                    targets: 3,
                    render: function (data, type, full, meta) {
                        return full.sku || '-';
                    }
                },
                {
                    // Quantity
                    targets: 4,
                    render: function (data, type, full, meta) {
                        const qty = full.quantity;
                        const min = full.min_quantity;
                        let color = 'success';
                        if (qty <= min) color = 'danger';
                        else if (qty <= min * 1.2) color = 'warning';

                        return `<span class="badge bg-label-${color}">${qty}</span>`;
                    }
                },
                {
                    // Selling Price
                    targets: 5,
                    render: function (data, type, full, meta) {
                        return `R$ ${parseFloat(full.selling_price).toFixed(2)}`;
                    }
                },
                {
                    // Location
                    targets: 6,
                    render: function (data, type, full, meta) {
                        return full.location || '-';
                    }
                },
                {
                    // Status
                    targets: 7,
                    className: 'text-center',
                    render: function (data, type, full, meta) {
                        const isActive = full.is_active;
                        return `${isActive
                            ? `<span class="badge bg-success">${t['Active'] || 'Ativo'}</span>`
                            : `<span class="badge bg-danger">${t['Inactive'] || 'Inativo'}</span>`
                            }`;
                    }
                },
                {
                    // Actions
                    targets: 8,
                    title: t['Actions'] || 'Ações',
                    searchable: false,
                    orderable: false,
                    render: function (data, type, full, meta) {
                        return (
                            '<div class="d-flex align-items-center gap-4">' +
                            `<button class="btn btn-sm btn-icon edit-record" data-id="${full.id}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddItems" title="${t['Edit'] || 'Editar'}"><i class="icon-base ti tabler-edit icon-22px"></i></button>` +
                            `<button class="btn btn-sm btn-icon publish-meli" data-id="${full.id}" data-name="${full.name}" data-price="${full.selling_price}" title="${t['Publish on Mercado Livre'] || 'Anunciar no Mercado Livre'}"><i class="icon-base ti tabler-share icon-22px text-warning"></i></button>` +
                            `<button class="btn btn-sm btn-icon delete-record" data-id="${full.id}" title="${t['Delete'] || 'Excluir'}"><i class="icon-base ti tabler-trash icon-22px"></i></button>` +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[2, 'asc']],
            layout: {
                topStart: {
                    rowClass: 'row m-3 my-0 justify-content-between',
                    features: [
                        {
                            pageLength: {
                                menu: [10, 20, 50, 100],
                                text: '_MENU_'
                            }
                        }
                    ]
                },
                topEnd: {
                    features: [
                        {
                            search: {
                                placeholder: t['Search Item'] || 'Procurar item',
                                text: '_INPUT_'
                            }
                        },
                        {
                            buttons: [
                                {
                                    text: `<i class="icon-base ti tabler-plus icon-sm me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">${t['Add Item'] || 'Adicionar Item'}</span>`,
                                    className: 'add-new btn btn-primary',
                                    attr: {
                                        'data-bs-toggle': 'offcanvas',
                                        'data-bs-target': '#offcanvasAddItems'
                                    }
                                }
                            ]
                        }
                    ]
                },
                bottomStart: {
                    rowClass: 'row mx-3 justify-content-between',
                    features: [
                        {
                            info: {
                                text: t['Showing _START_ to _END_ of _TOTAL_ entries'] || 'Showing _START_ to _END_ of _TOTAL_ entries'
                            }
                        }
                    ]
                },
                bottomEnd: 'paging'
            },
            displayLength: 10,
            language: {
                paginate: {
                    first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
                    last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>',
                    next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
                    previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>'
                }
            },
            responsive: {
                details: {
                    display: DataTable.Responsive.display.modal({
                        header: function (row) {
                            const data = row.data();
                            return (t['Details of'] || 'Detalhes de') + ' ' + data.name;
                        }
                    }),
                    type: 'column',
                    renderer: function (api, rowIdx, columns) {
                        const data = columns
                            .map(function (col) {
                                return col.title !== ''
                                    ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                      <td>${col.title}:</td>
                      <td>${col.data}</td>
                    </tr>`
                                    : '';
                            })
                            .join('');

                        if (data) {
                            const div = document.createElement('div');
                            div.classList.add('table-responsive');
                            const table = document.createElement('table');
                            div.appendChild(table);
                            table.classList.add('table');
                            const tbody = document.createElement('tbody');
                            tbody.innerHTML = data;
                            table.appendChild(tbody);
                            return div;
                        }
                        return false;
                    }
                }
            },
            initComplete: function () {
                document.querySelectorAll('.dt-buttons .btn').forEach(btn => {
                    btn.classList.remove('btn-secondary');
                });
            }
        });

        // Delete Record
        document.addEventListener('click', function (e) {
            if (e.target.closest('.delete-record')) {
                const deleteBtn = e.target.closest('.delete-record');
                const id = deleteBtn.dataset.id;

                Swal.fire({
                    title: t['Are you sure?'] || 'Você tem certeza?',
                    text: t["You won't be able to revert this!"] || "Você não poderá reverter isso!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: t['Yes, delete it!'] || 'Sim, exclua!',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3',
                        cancelButton: 'btn btn-label-secondary'
                    },
                    buttonsStyling: false
                }).then(function (result) {
                    if (result.value) {
                        fetch(`${baseUrl}inventory/items/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            }
                        })
                            .then(response => {
                                if (response.ok) {
                                    dt_items.draw();
                                    Swal.fire({
                                        icon: 'success',
                                        title: t['Deleted!'] || 'Excluído!',
                                        text: t['The item has been deleted!'] || 'O item foi excluído!',
                                        customClass: { confirmButton: 'btn btn-success' }
                                    });
                                } else {
                                    throw new Error('Delete failed');
                                }
                            })
                            .catch(error => {
                                console.log(error);
                            });
                    }
                });
            }
        });

        // edit record
        document.addEventListener('click', function (e) {
            if (e.target.closest('.edit-record')) {
                const editBtn = e.target.closest('.edit-record');
                const id = editBtn.dataset.id;

                // changing the title of offcanvas
                document.getElementById('offcanvasAddItemsLabel').innerHTML = t['Edit Item'] || 'Editar Item';

                // get data
                fetch(`${baseUrl}inventory/items/${id}/edit`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('item_id').value = data.id;
                        document.getElementById('add-item-name').value = data.name;
                        
                        if (data.main_image) {
                            uploadedAvatar.src = baseUrl + 'storage/' + data.main_image.path;
                        } else {
                            uploadedAvatar.src = baseUrl + 'assets/img/elements/food-placeholder.png';
                        }
                        document.getElementById('add-item-sku').value = data.sku || '';
                        document.getElementById('add-item-cost').value = data.cost_price;
                        document.getElementById('add-item-price').value = data.selling_price;
                        document.getElementById('add-item-quantity').value = data.quantity;
                        document.getElementById('add-item-min-quantity').value = data.min_quantity;
                        document.getElementById('add-item-unit').value = data.unit;
                        document.getElementById('add-item-location').value = data.location || '';
                        document.getElementById('add-item-description').value = data.description || '';

                        const $supplier = $('#add-item-supplier');
                        if ($supplier.length && $supplier.hasClass('select2-hidden-accessible')) {
                            $supplier.val(data.supplier_id).trigger('change');
                        }

                        const $category = $('#add-item-category');
                        if ($category.length && $category.hasClass('select2-hidden-accessible')) {
                            $category.val(data.menu_category_id).trigger('change');
                        }
                    });
            }
        });

        // Reset form when adding new
        const addNewBtn = document.querySelector('.add-new');
        if (addNewBtn) {
            addNewBtn.addEventListener('click', function () {
                document.getElementById('item_id').value = '';
                uploadedAvatar.src = baseUrl + 'assets/img/elements/food-placeholder.png';
                document.getElementById('offcanvasAddItemsLabel').innerHTML = t['Add Item'] || 'Adicionar Item';
                document.getElementById('addNewItemsForm').reset();
                $('#add-item-supplier').val(null).trigger('change');
            });
        }

        // Timeout for styles
        setTimeout(() => {
            // ... same style adjustments ...
            const elementsToModify = [
                { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
                { selector: '.dt-search .form-control', classToRemove: 'form-control-sm' },
                { selector: '.dt-length .form-select', classToRemove: 'form-select-sm', classToAdd: 'ms-0' },
                { selector: '.dt-length', classToAdd: 'mb-md-6 mb-0' },
                {
                    selector: '.dt-layout-end',
                    classToRemove: 'justify-content-between',
                    classToAdd: 'd-flex gap-md-4 justify-content-md-between justify-content-center gap-2 flex-wrap'
                },
                { selector: '.dt-buttons', classToAdd: 'd-flex gap-4 mb-md-0 mb-4' }
            ];
            elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
                document.querySelectorAll(selector).forEach(element => {
                    if (classToRemove) classToRemove.split(' ').forEach(className => element.classList.remove(className));
                    if (classToAdd) classToAdd.split(' ').forEach(className => element.classList.add(className));
                });
            });
        }, 100);
    }

    // Form Validation
    const addNewItemsForm = document.getElementById('addNewItemsForm');
    if (addNewItemsForm) {
        const fv = FormValidation.formValidation(addNewItemsForm, {
            fields: {
                name: { validators: { notEmpty: { message: t['Please fill in the item name'] || 'Por favor preencha o nome do item' } } },
                cost_price: { validators: { notEmpty: { message: t['Please fill in the cost'] || 'Por favor preencha o custo' } } },
                selling_price: { validators: { notEmpty: { message: t['Please fill in the selling price'] || 'Por favor preencha o preço de venda' } } },
                quantity: { validators: { notEmpty: { message: t['Please fill in the quantity'] || 'Por favor preencha a quantidade' } } },
                min_quantity: { validators: { notEmpty: { message: t['Please fill in the minimum stock'] || 'Por favor preencha o estoque mínimo' } } },
                unit: { validators: { notEmpty: { message: t['Please fill in the unit'] || 'Por favor preencha a unidade' } } }
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: '',
                    rowSelector: '.mb-6'
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus()
            }
        }).on('core.form.valid', function () {
            const formData = new FormData(addNewItemsForm);
            
            // Determine method (POST or spoofed PUT) based on ID existence
            const id = formData.get('id');
            const url = id ? `${baseUrl}inventory/items/${id}` : `${baseUrl}inventory/items`;
            
            if (id) {
                formData.append('_method', 'PUT');
            }

            fetch(url, {
                method: 'POST', // Use POST with _method spoofing for file uploads in PUT requests
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            // Limpar erros anteriores
                            addNewItemsForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                            addNewItemsForm.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

                            Object.keys(data.errors).forEach(key => {
                                const input = addNewItemsForm.querySelector(`[name="${key}"]`);
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
                            return; // Encerra sem fechar o offcanvas
                        }
                        throw new Error(data.message || 'Erro inesperado');
                    }

                    dt_items_table && new DataTable(dt_items_table).draw();
                    const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasForm);
                    offcanvasInstance && offcanvasInstance.hide();

                    if (!id) { // Só pergunta se for um novo item
                        Swal.fire({
                            icon: 'success',
                            title: t['Item Created!'] || 'Item Criado!',
                            text: t['Do you want to generate the QR Code label for this item now?'] || 'Deseja gerar a etiqueta com QR Code para este item agora?',
                            showCancelButton: true,
                            confirmButtonText: t['Yes, Generate Label'] || 'Sim, Gerar Etiqueta',
                            cancelButtonText: t['Not Now'] || 'Agora Não',
                            customClass: {
                                confirmButton: 'btn btn-primary me-3',
                                cancelButton: 'btn btn-label-secondary'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open(baseUrl + "inventory/items/" + data.data.id + "/print-label", "_blank");
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: t['Updated!'] || 'Atualizado!',
                            text: data.message,
                            customClass: { confirmButton: 'btn btn-success' }
                        });
                    }
                })
                .catch(err => {
                    if (err.message) {
                        Swal.fire({
                            title: 'Erro!',
                            text: err.message,
                            icon: 'error',
                            customClass: { confirmButton: 'btn btn-success' }
                        });
                    }
                });
        });

        offCanvasForm.addEventListener('show.bs.offcanvas', function () {
            const itemId = document.getElementById('item_id').value;
            const recipeSection = document.getElementById('recipe-section');
            const recipeContainer = document.getElementById('livewire-recipe-container');

            if (recipeSection) {
                if (itemId) {
                    recipeSection.classList.remove('d-none');
                    // Injeta o componente Livewire dinamicamente
                    recipeContainer.innerHTML = `<livewire:product-recipe-manager :product-id="${itemId}" :key="${itemId}" />`;
                    // Força o Livewire a re-escanear o DOM
                    if (window.Livewire) {
                        window.Livewire.rescan();
                    }
                } else {
                    recipeSection.classList.add('d-none');
                    recipeContainer.innerHTML = `<p class="text-muted small">${t['Save the item first to enable ingredient configuration.'] || 'Salve o item primeiro para liberar a configuração de ingredientes.'}</p>`;
                }
            }
        });

        offCanvasForm.addEventListener('hidden.bs.offcanvas', function () {
            fv.resetForm(true);
        });
    }

    // Mercado Livre Publication Logic
    document.addEventListener('click', function (e) {
        if (e.target.closest('.publish-meli')) {
            const btn = e.target.closest('.publish-meli');
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const price = btn.dataset.price;

            document.getElementById('publish_item_id').value = id;
            document.getElementById('publish_item_name').value = name;
            document.getElementById('publish_item_price').value = price;

            const modal = new bootstrap.Modal(document.getElementById('modalPublishMeli'));
            modal.show();
        }
    });

    const formPublishMeli = document.getElementById('formPublishMeli');
    if (formPublishMeli) {
        formPublishMeli.addEventListener('submit', function (e) {
            e.preventDefault();
            const submitBtn = formPublishMeli.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status"></span> ${t['Publishing...'] || 'Publicando...'}`;

            const formData = new FormData(formPublishMeli);

            fetch(`${baseUrl}meli/publish`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = t['Publish Now'] || 'Publicar Agora';

                    if (data.success) {
                        const modalElement = document.getElementById('modalPublishMeli');
                        const modalInstance = bootstrap.Modal.getInstance(modalElement);
                        modalInstance.hide();

                        Swal.fire({
                            title: t['Success!'] || 'Sucesso!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: t['View Ad'] || 'Ver Anúncio',
                            showCancelButton: true,
                            cancelButtonText: t['Close'] || 'Fechar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open(data.url, '_blank');
                            }
                        });
                    } else {
                        Swal.fire({
                            title: t['Error!'] || 'Erro!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: t['Understood'] || 'Entendido'
                        });
                    }
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = t['Publish Now'] || 'Publicar Agora';
                    Swal.fire({
                        title: t['Error!'] || 'Erro!',
                        text: 'Erro ao tentar se conectar com o servidor.',
                        icon: 'error',
                        confirmButtonText: t['Understood'] || 'Entendido'
                    });
                });
        });
    }
});
