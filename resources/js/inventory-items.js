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

    const dt_items_table = document.querySelector('.datatables-items');
    const offCanvasForm = document.getElementById('offcanvasAddItems');

    const t = window.inventoryTranslations || {};

    // Items datatable
    if (dt_items_table) {
        const dt_items = new DataTable(dt_items_table, {
            processing: true,
            serverSide: true,
            ajax: {
                url: baseUrl + 'inventory/items-list',
                data: function (d) {
                    d.type = $('.filter-type-btn.active').data('type') || 'all';
                },
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
                { data: 'profit' },
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
                    // Item (Name + Category + Type)
                    targets: 2,
                    responsivePriority: 4,
                    render: function (data, type, full, meta) {
                        const typeBadge = full.is_ingredient
                            ? '<span class="badge bg-label-warning me-1" style="font-size: 0.65rem;">INSUMO</span>'
                            : '<span class="badge bg-label-info me-1" style="font-size: 0.65rem;">VENDA</span>';

                        return `
                        <div class="d-flex flex-column">
                            <span class="fw-medium text-heading text-truncate">${full.name}</span>
                            <div class="d-flex align-items-center mt-1">
                                ${typeBadge}
                                <small class="text-muted text-truncate">${full.category_name}</small>
                            </div>
                        </div>`;
                    }
                },
                {
                    // SKU
                    targets: 3,
                    render: function (data, type, full, meta) {
                        return `<span class="text-muted small">${full.sku || '-'}</span>`;
                    }
                },
                {
                    // Stock (Qty + Unit)
                    targets: 4,
                    render: function (data, type, full, meta) {
                        const qty = full.quantity;
                        const min = full.min_quantity;
                        let color = 'success';
                        if (qty <= min) color = 'danger';
                        else if (qty <= min * 1.2) color = 'warning';

                        return `
                        <div class="d-flex flex-column align-items-start">
                            <span class="badge bg-label-${color}">${qty} ${full.unit}</span>
                            <small class="text-muted mt-1" style="font-size: 0.7rem;">Mín: ${min}</small>
                        </div>`;
                    }
                },
                {
                    // Cost / Sale Price
                    targets: 5,
                    render: function (data, type, full, meta) {
                        const cost = parseFloat(full.cost_price).toFixed(2);
                        const sale = parseFloat(full.selling_price).toFixed(2);

                        return `
                        <div class="d-flex flex-column">
                            <div class="d-flex justify-content-between gap-3">
                                <small class="text-muted small">Custo:</small>
                                <span class="fw-medium small">R$ ${cost}</span>
                            </div>
                            ${full.is_for_sale ? `
                            <div class="d-flex justify-content-between gap-3">
                                <small class="text-primary font-weight-bold small">Venda:</small>
                                <span class="fw-bold small text-primary">R$ ${sale}</span>
                            </div>` : '<small class="text-muted italic small">Apenas uso interno</small>'}
                        </div>`;
                    }
                },
                {
                    // Profit / Margin (Automatic Calculation)
                    targets: 6,
                    render: function (data, type, full, meta) {
                        if (!full.is_for_sale || parseFloat(full.selling_price) <= 0) return '<span class="text-muted small">-</span>';
                        
                        const cost = parseFloat(full.cost_price);
                        const sale = parseFloat(full.selling_price);
                        const profit = sale - cost;
                        const margin = ((sale - cost) / sale) * 100;
                        
                        let color = 'success';
                        if (margin < 20) color = 'danger';
                        else if (margin < 40) color = 'warning';

                        return `
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-${color} small">R$ ${profit.toFixed(2)}</span>
                                <small class="text-${color}" style="font-size: 0.7rem;">${margin.toFixed(1)}% margem</small>
                            </div>`;
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
                                    className: 'add-new btn btn-primary ms-3',
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
            }
        });

        // Filter buttons logic
        $('.filter-type-btn').on('click', function () {
            $('.filter-type-btn').removeClass('active btn-primary').addClass('btn-label-secondary');
            $(this).addClass('active btn-primary').removeClass('btn-label-secondary');
            dt_items.draw();
        });

        // Delete Record
        $(document).on('click', '.delete-record', function () {
            const id = $(this).data('id');
            Swal.fire({
                title: t['Are you sure?'] || 'Tem certeza?',
                text: t["You won't be able to revert this!"] || "Você não poderá reverter isso!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: t['Yes, delete it!'] || 'Sim, excluir!',
                cancelButtonText: t['Cancel'] || 'Cancelar',
                customClass: {
                    confirmButton: 'btn btn-primary me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then(function (result) {
                if (result.value) {
                    $.ajax({
                        type: 'DELETE',
                        url: `${baseUrl}inventory/items/${id}`,
                        success: function () {
                            dt_items.draw();
                            Swal.fire({
                                icon: 'success',
                                title: t['Deleted!'] || 'Excluído!',
                                text: t['Item has been deleted.'] || 'O item foi excluído.',
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            });
                        },
                        error: function () {
                            Swal.fire({
                                title: t['Error!'] || 'Erro!',
                                text: t['Error deleting item.'] || 'Erro ao excluir item.',
                                icon: 'error',
                                confirmButtonText: t['Understood'] || 'Entendido'
                            });
                        }
                    });
                }
            });
        });

        // Edit Record
        $(document).on('click', '.edit-record', function () {
            const id = $(this).data('id');
            const offcanvasTitle = document.getElementById('offcanvasAddItemsLabel');
            offcanvasTitle.innerHTML = t['Edit Item'] || 'Editar Item';

            $.get(`${baseUrl}inventory/items/${id}/edit`, function (data) {
                $('#item_id').val(data.id);
                $('#add-item-name').val(data.name);
                $('#add-item-sku').val(data.sku);
                $('#add-item-description').val(data.description);
                $('#add-item-cost').val(data.cost_price);
                $('#add-item-selling').val(data.selling_price);
                $('#add-item-quantity').val(data.quantity);
                $('#add-item-min-quantity').val(data.min_quantity);
                $('#add-item-unit').val(data.unit).trigger('change');
                $('#add-item-supplier').val(data.supplier_id).trigger('change');
                $('#add-item-location').val(data.location);
                
                const $category = $('#add-item-category');
                if ($category.length) {
                    $category.val(data.menu_category_id).trigger('change');
                }

                // Checkboxes
                $('#add-item-is-ingredient').prop('checked', data.is_ingredient == 1);
                $('#add-item-is-for-sale').prop('checked', data.is_for_sale == 1);
            });
        });
    }

    // Select2 initialization
    var select2 = $('.select2');
    if (select2.length) {
        select2.each(function () {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
                placeholder: $this.find('option:first').text() || t['Select'] || 'Selecione',
                dropdownParent: $this.parent(),
                allowClear: true,
                language: {
                    noResults: function () { return t['No results found'] || "Nenhum resultado encontrado"; },
                    searching: function () { return t['Searching...'] || "Procurando..."; },
                    loadingMore: function () { return t['Loading more results...'] || "Carregando mais resultados..."; }
                }
            });
        });
    }

    if (offCanvasForm) {
        offCanvasForm.addEventListener('show.bs.offcanvas', function () {
            const itemId = document.getElementById('item_id').value;
            const recipeSection = document.getElementById('recipe-section');
            
            // Re-init select2 inside offcanvas
            $('#offcanvasAddItems .select2').each(function () {
                var $this = $(this);
                if ($this.hasClass('select2-hidden-accessible')) {
                    $this.select2('destroy');
                }
                $this.wrap('<div class="position-relative"></div>').select2({
                    placeholder: $this.find('option:first').text() || t['Select'] || 'Selecione',
                    dropdownParent: $this.parent(),
                    allowClear: true,
                    language: {
                        noResults: function () { return t['No results found'] || "Nenhum resultado encontrado"; },
                        searching: function () { return t['Searching...'] || "Procurando..."; },
                        loadingMore: function () { return t['Loading more results...'] || "Carregando mais resultados..."; }
                    }
                });
            });

            if (recipeSection) {
                if (itemId) {
                    recipeSection.classList.remove('d-none');
                    if (window.Livewire) {
                        window.Livewire.dispatch('load-product-recipe', { productId: itemId });
                    }
                } else {
                    recipeSection.classList.add('d-none');
                }
            }
        });

        offCanvasForm.addEventListener('hidden.bs.offcanvas', function () {
            document.getElementById('item_id').value = '';
            document.getElementById('offcanvasAddItemsLabel').innerHTML = t['Add Item'] || 'Adicionar Item';
            $('#recipe-section').addClass('d-none');
            // Reset form
            document.getElementById('addNewItemForm').reset();
            $('.select2').val('').trigger('change');
        });
    }

    // ajax setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Form Submission
    const addNewItemForm = document.getElementById('addNewItemForm');
    if (addNewItemForm) {
        addNewItemForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const itemId = document.getElementById('item_id').value;
            const url = itemId ? `${baseUrl}inventory/items/${itemId}` : `${baseUrl}inventory/items`;
            const method = itemId ? 'POST' : 'POST'; // We use POST with _method PUT for updates

            let formData = new FormData(this);
            if (itemId) {
                formData.append('_method', 'PUT');
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (data) {
                    bootstrap.Offcanvas.getInstance(offCanvasForm).hide();
                    const dt = new DataTable(dt_items_table);
                    dt.draw();
                    Swal.fire({
                        title: t['Success!'] || 'Sucesso!',
                        text: itemId ? (t['Item updated successfully!'] || 'Item atualizado com sucesso!') : (t['Item added successfully!'] || 'Item adicionado com sucesso!'),
                        icon: 'success',
                        confirmButtonText: t['Understood'] || 'Entendido'
                    });
                },
                error: function (xhr) {
                    let errorMessage = 'Erro ao salvar item.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: t['Error!'] || 'Erro!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: t['Understood'] || 'Entendido'
                    });
                }
            });
        });
    }
});
