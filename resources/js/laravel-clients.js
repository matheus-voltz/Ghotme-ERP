/**
 * Page User management
 */

'use strict';

// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
    let borderColor, bodyBg, headingColor;

    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;

    // Variable declaration for table
    const dt_clients_table = document.querySelector('.datatables-clients'),
        clientsView = baseUrl + 'app/clients/view/account',
        clientsSuspend = baseUrl + 'app/clients/suspend/account',
        offCanvasForm = document.getElementById('offcanvasAddClients');

    // Select2 initialization
    var select2 = $('.select2');
    if (select2.length) {
        var $this = select2;
        $this.wrap('<div class="position-relative"></div>').select2({
            placeholder: 'Select Country',
            dropdownParent: $this.parent()
        });
    }

    // ajax setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Clients datatable
    if (dt_clients_table) {
        const dt_clients = new DataTable(dt_clients_table, {
            processing: true,
            serverSide: true,
            ajax: {
                url: baseUrl + 'clients-list',
                dataSrc: function (json) {
                    // Ensure recordsTotal and recordsFiltered are numeric and not undefined/null
                    if (typeof json.recordsTotal !== 'number') {
                        json.recordsTotal = 0;
                    }
                    if (typeof json.recordsFiltered !== 'number') {
                        json.recordsFiltered = 0;
                    }

                    // Fallback for empty data to avoid pagination NaN issue
                    json.data = Array.isArray(json.data) ? json.data : [];

                    return json.data;
                }
            },
            columns: [
                // columns according to JSON
                { data: 'fake_id' },
                { data: 'id' },
                { data: 'type' },
                { data: 'name' },
                { data: 'email' },
                { data: 'company_name' },
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
                    // Type
                    targets: 2,
                    render: function (data, type, full, meta) {
                        const clientType = full['type'];
                        const color = clientType === 'PJ' ? 'info' : 'success';
                        return `<span class="badge bg-label-${color}">${clientType}</span>`;
                    }
                },
                {
                    // Client Name
                    targets: 3,
                    responsivePriority: 4,
                    render: function (data, type, full, meta) {
                        const { name, company_name, type: clientType } = full;
                        // Display name logic: priority to company_name if PJ, else name
                        const displayName = clientType === 'PJ' ? company_name : name;

                        // For Avatar badge
                        const stateNum = Math.floor(Math.random() * 6);
                        const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
                        const state = states[stateNum];

                        // Extract initials
                        let initials = (displayName.match(/\b\w/g) || []).shift() || '';
                        if ((displayName.match(/\b\w/g) || []).length > 1) {
                            initials += (displayName.match(/\b\w/g) || []).pop();
                        }
                        const initialsUpper = initials.toUpperCase();

                        const avatar = `<span class="avatar-initial rounded-circle bg-label-${state}">${initialsUpper}</span>`;

                        const rowOutput = `
                            <div class="d-flex justify-content-start align-items-center user-name">
                                <div class="avatar-wrapper">
                                <div class="avatar avatar-sm me-4">
                                    ${avatar}
                                </div>
                                </div>
                                <div class="d-flex flex-column">
                                <a href="${clientsView}" class="text-truncate text-heading">
                                    <span class="fw-medium">${displayName}</span>
                                </a>
                                </div>
                            </div>
                        `;
                        return rowOutput;
                    }
                },
                {
                    // Email
                    targets: 4,
                    render: function (data, type, full, meta) {
                        return '<span class="user-email">' + full['email'] + '</span>';
                    }
                },
                {
                    // Company Name (Extra column if needed, or maybe verified status?)
                    targets: 5,
                    className: 'text-center',
                    render: function (data, type, full, meta) {
                        return full['company_name'] || '-';
                    }
                },
                {
                    // status
                    targets: 6,
                    className: 'text-center',
                    render: function (data, type, full, meta) {
                        const isActive = full['is_active'];
                        return `${isActive
                            ? '<span class="badge bg-success">Ativo</span>'
                            : '<span class="badge bg-danger">Inativo</span>'
                            }`;
                    }
                },
                {
                    // Actions
                    targets: 7,
                    title: 'Ações',
                    searchable: false,
                    orderable: false,
                    render: function (data, type, full, meta) {
                        return (
                            '<div class="d-flex align-items-center gap-4">' +
                            `<button class="btn btn-sm btn-icon edit-record" data-id="${full['id']}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddClients"><i class="icon-base ti tabler-edit icon-22px"></i></button>` +
                            `<button class="btn btn-sm btn-icon delete-record" data-id="${full['id']}"><i class="icon-base ti tabler-trash icon-22px"></i></button>` +
                            '<button class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="icon-base ti tabler-dots-vertical icon-22px"></i></button>' +
                            '<div class="dropdown-menu dropdown-menu-end m-0">' +
                            '<a href="' +
                            clientsView +
                            '" class="dropdown-item">View</a>' +
                            '<a href="' + clientsSuspend + '" class="dropdown-item">Suspend</a>' +
                            '</div>' +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[2, 'desc']],
            layout: {
                topStart: {
                    rowClass: 'row m-3 my-0 justify-content-between',
                    features: [
                        {
                            pageLength: {
                                menu: [7, 10, 20, 50, 70, 100],
                                text: '_MENU_'
                            }
                        }
                    ]
                },
                topEnd: {
                    features: [
                        {
                            search: {
                                placeholder: 'Procurar usuario',
                                text: '_INPUT_'
                            }
                        },
                        {
                            buttons: [
                                {
                                    extend: 'collection',
                                    className: 'btn btn-label-secondary dropdown-toggle',
                                    text: '<i class="icon-base ti tabler-upload me-2 icon-sm"></i>Exportar',
                                    buttons: [
                                        {
                                            extend: 'print',
                                            title: 'Users',
                                            text: '<i class="icon-base ti tabler-printer me-2" ></i>Imprimir',
                                            className: 'dropdown-item',
                                            exportOptions: {
                                                columns: [1, 2, 3, 4, 5],
                                                // prevent avatar to be print
                                                format: {
                                                    body: function (inner, coldex, rowdex) {
                                                        if (inner.length <= 0) return inner;

                                                        // Check if inner is HTML content
                                                        if (inner.indexOf('<') > -1) {
                                                            const parser = new DOMParser();
                                                            const doc = parser.parseFromString(inner, 'text/html');

                                                            // Get all text content
                                                            let text = '';

                                                            // Handle specific elements
                                                            const userNameElements = doc.querySelectorAll('.user-name');
                                                            if (userNameElements.length > 0) {
                                                                userNameElements.forEach(el => {
                                                                    // Get text from nested structure
                                                                    const nameText =
                                                                        el.querySelector('.fw-medium')?.textContent ||
                                                                        el.querySelector('.d-block')?.textContent ||
                                                                        el.textContent;
                                                                    text += nameText.trim() + ' ';
                                                                });
                                                            } else {
                                                                // Get regular text content
                                                                text = doc.body.textContent || doc.body.innerText;
                                                            }

                                                            return text.trim();
                                                        }

                                                        return inner;
                                                    }
                                                }
                                            },
                                            customize: function (win) {
                                                win.document.body.style.color = config.colors.headingColor;
                                                win.document.body.style.borderColor = config.colors.borderColor;
                                                win.document.body.style.backgroundColor = config.colors.bodyBg;
                                                const table = win.document.body.querySelector('table');
                                                table.classList.add('compact');
                                                table.style.color = 'inherit';
                                                table.style.borderColor = 'inherit';
                                                table.style.backgroundColor = 'inherit';
                                            }
                                        },
                                        {
                                            extend: 'csv',
                                            title: 'Users',
                                            text: '<i class="icon-base ti tabler-file-text me-2" ></i>Csv',
                                            className: 'dropdown-item',
                                            exportOptions: {
                                                columns: [1, 2, 3, 4, 5],
                                                format: {
                                                    body: function (inner, coldex, rowdex) {
                                                        if (inner.length <= 0) return inner;

                                                        // Parse HTML content
                                                        const parser = new DOMParser();
                                                        const doc = parser.parseFromString(inner, 'text/html');

                                                        let text = '';

                                                        // Handle user-name elements specifically
                                                        const userNameElements = doc.querySelectorAll('.user-name');
                                                        if (userNameElements.length > 0) {
                                                            userNameElements.forEach(el => {
                                                                // Get text from nested structure - try different selectors
                                                                const nameText =
                                                                    el.querySelector('.fw-medium')?.textContent ||
                                                                    el.querySelector('.d-block')?.textContent ||
                                                                    el.textContent;
                                                                text += nameText.trim() + ' ';
                                                            });
                                                        } else {
                                                            // Handle other elements (status, role, etc)
                                                            text = doc.body.textContent || doc.body.innerText;
                                                        }

                                                        return text.trim();
                                                    }
                                                }
                                            }
                                        },
                                        {
                                            extend: 'excel',
                                            text: '<i class="icon-base ti tabler-file-spreadsheet me-2"></i>Excel',
                                            className: 'dropdown-item',
                                            exportOptions: {
                                                columns: [1, 2, 3, 4, 5],
                                                format: {
                                                    body: function (inner, coldex, rowdex) {
                                                        if (inner.length <= 0) return inner;

                                                        // Parse HTML content
                                                        const parser = new DOMParser();
                                                        const doc = parser.parseFromString(inner, 'text/html');

                                                        let text = '';

                                                        // Handle user-name elements specifically
                                                        const userNameElements = doc.querySelectorAll('.user-name');
                                                        if (userNameElements.length > 0) {
                                                            userNameElements.forEach(el => {
                                                                // Get text from nested structure - try different selectors
                                                                const nameText =
                                                                    el.querySelector('.fw-medium')?.textContent ||
                                                                    el.querySelector('.d-block')?.textContent ||
                                                                    el.textContent;
                                                                text += nameText.trim() + ' ';
                                                            });
                                                        } else {
                                                            // Handle other elements (status, role, etc)
                                                            text = doc.body.textContent || doc.body.innerText;
                                                        }

                                                        return text.trim();
                                                    }
                                                }
                                            }
                                        },
                                        {
                                            extend: 'pdf',
                                            title: 'Users',
                                            text: '<i class="icon-base ti tabler-file-code-2 me-2"></i>Pdf',
                                            className: 'dropdown-item',
                                            exportOptions: {
                                                columns: [1, 2, 3, 4, 5],
                                                format: {
                                                    body: function (inner, coldex, rowdex) {
                                                        if (inner.length <= 0) return inner;

                                                        // Parse HTML content
                                                        const parser = new DOMParser();
                                                        const doc = parser.parseFromString(inner, 'text/html');

                                                        let text = '';

                                                        // Handle user-name elements specifically
                                                        const userNameElements = doc.querySelectorAll('.user-name');
                                                        if (userNameElements.length > 0) {
                                                            userNameElements.forEach(el => {
                                                                // Get text from nested structure - try different selectors
                                                                const nameText =
                                                                    el.querySelector('.fw-medium')?.textContent ||
                                                                    el.querySelector('.d-block')?.textContent ||
                                                                    el.textContent;
                                                                text += nameText.trim() + ' ';
                                                            });
                                                        } else {
                                                            // Handle other elements (status, role, etc)
                                                            text = doc.body.textContent || doc.body.innerText;
                                                        }

                                                        return text.trim();
                                                    }
                                                }
                                            }
                                        },
                                        {
                                            extend: 'copy',
                                            title: 'Users',
                                            text: '<i class="icon-base ti tabler-copy me-2" ></i>Copiar',
                                            className: 'dropdown-item',
                                            exportOptions: {
                                                columns: [1, 2, 3, 4, 5],
                                                format: {
                                                    body: function (inner, coldex, rowdex) {
                                                        if (inner.length <= 0) return inner;

                                                        // Parse HTML content
                                                        const parser = new DOMParser();
                                                        const doc = parser.parseFromString(inner, 'text/html');

                                                        let text = '';

                                                        // Handle user-name elements specifically
                                                        const userNameElements = doc.querySelectorAll('.user-name');
                                                        if (userNameElements.length > 0) {
                                                            userNameElements.forEach(el => {
                                                                // Get text from nested structure - try different selectors
                                                                const nameText =
                                                                    el.querySelector('.fw-medium')?.textContent ||
                                                                    el.querySelector('.d-block')?.textContent ||
                                                                    el.textContent;
                                                                text += nameText.trim() + ' ';
                                                            });
                                                        } else {
                                                            // Handle other elements (status, role, etc)
                                                            text = doc.body.textContent || doc.body.innerText;
                                                        }

                                                        return text.trim();
                                                    }
                                                }
                                            }
                                        }
                                    ]
                                },
                                {
                                    text: '<i class="icon-base ti tabler-plus icon-sm me-0 me-sm-2"></i><span class="d-none d-sm-inline-block">Adicionar novo usuario</span>',
                                    className: 'add-new btn btn-primary',
                                    attr: {
                                        'data-bs-toggle': 'offcanvas',
                                        'data-bs-target': '#offcanvasAddUser'
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
                                text: 'Showing _START_ to _END_ of _TOTAL_ entries'
                            }
                        }
                    ]
                },
                bottomEnd: 'paging'
            },
            displayLength: 7,
            language: {
                paginate: {
                    first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
                    last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>',
                    next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
                    previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>'
                }
            },
            // For responsive popup
            responsive: {
                details: {
                    display: DataTable.Responsive.display.modal({
                        header: function (row) {
                            const data = row.data();
                            return 'Details of ' + data['name'];
                        }
                    }),
                    type: 'column',
                    renderer: function (api, rowIdx, columns) {
                        const data = columns
                            .map(function (col) {
                                return col.title !== '' // Do not show row in modal popup if title is blank (for check box)
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
                // Remove btn-secondary from export buttons
                document.querySelectorAll('.dt-buttons .btn').forEach(btn => {
                    btn.classList.remove('btn-secondary');
                });
            }
        });

        // Delete Record
        document.addEventListener('click', function (e) {
            if (e.target.closest('.delete-record')) {
                const deleteBtn = e.target.closest('.delete-record');
                const user_id = deleteBtn.dataset.id;
                const dtrModal = document.querySelector('.dtr-bs-modal.show');

                // hide responsive modal in small screen
                if (dtrModal) {
                    const bsModal = bootstrap.Modal.getInstance(dtrModal);
                    bsModal.hide();
                }

                // sweetalert for confirmation of delete
                Swal.fire({
                    title: 'Você tem certeza?',
                    text: "Você não poderá reverter isso!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, exclua!',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3',
                        cancelButton: 'btn btn-label-secondary'
                    },
                    buttonsStyling: false
                }).then(function (result) {
                    if (result.value) {
                        // delete the data
                        fetch(`${baseUrl}user-list/${user_id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            }
                        })
                            .then(response => {
                                if (response.ok) {
                                    dt_user.draw();

                                    // success sweetalert
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Excluído!',
                                        text: 'O usuário foi excluído!',
                                        customClass: {
                                            confirmButton: 'btn btn-success'
                                        }
                                    });
                                } else {
                                    throw new Error('Delete failed');
                                }
                            })
                            .catch(error => {
                                console.log(error);
                            });
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        Swal.fire({
                            title: 'Cancelado',
                            text: 'O usuário não foi excluído!',
                            icon: 'error',
                            customClass: {
                                confirmButton: 'btn btn-success'
                            }
                        });
                    }
                });
            }
        });

        // edit record
        document.addEventListener('click', function (e) {
            if (e.target.closest('.edit-record')) {
                const editBtn = e.target.closest('.edit-record');
                const user_id = editBtn.dataset.id;
                const dtrModal = document.querySelector('.dtr-bs-modal.show');

                // hide responsive modal in small screen
                if (dtrModal) {
                    const bsModal = bootstrap.Modal.getInstance(dtrModal);
                    bsModal.hide();
                }

                // changing the title of offcanvas
                document.getElementById('offcanvasAddUserLabel').innerHTML = 'Edit User';

                // get data
                fetch(`${baseUrl}user-list/${user_id}/edit`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('user_id').value = data.id;
                        document.getElementById('add-user-fullname').value = data.name;
                        document.getElementById('add-user-email').value = data.email;
                        document.getElementById('add-user-contact').value = data.contact_number || '';
                        document.getElementById('add-user-company').value = data.company || '';


                        setVal('country', data.country);
                        const $country = $('#country');
                        if ($country.length && $country.hasClass('select2-hidden-accessible')) {
                            $country.val(data.country).trigger('change');
                        }

                        // role (se for Select2, precisa trigger)
                        setVal('user-role', data.role);
                        const $role = $('#user-role');
                        if ($role.length && $role.hasClass('select2-hidden-accessible')) {
                            $role.val(data.role).trigger('change');
                        }

                        // plan (se existir no form)
                        setVal('user-plan', data.plan);
                        const $plan = $('#user-plan');
                        if ($plan.length && $plan.hasClass('select2-hidden-accessible')) {
                            $plan.val(data.plan).trigger('change');
                        }
                    });
            }
        });

        // changing the title
        const addNewBtn = document.querySelector('.add-new');
        if (addNewBtn) {
            addNewBtn.addEventListener('click', function () {
                document.getElementById('user_id').value = ''; //resetting input field
                document.getElementById('offcanvasAddUserLabel').innerHTML = 'Adicionar novo usuario';
            });
        }

        // Filter form control to default size
        setTimeout(() => {
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
                { selector: '.dt-buttons', classToAdd: 'd-flex gap-4 mb-md-0 mb-4' },
                { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
                { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' }
            ];

            // Delete record
            elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
                document.querySelectorAll(selector).forEach(element => {
                    if (classToRemove) {
                        classToRemove.split(' ').forEach(className => element.classList.remove(className));
                    }
                    if (classToAdd) {
                        classToAdd.split(' ').forEach(className => element.classList.add(className));
                    }
                });
            });
        }, 100);
    }

    // validating form and updating user's data
    const addNewUserForm = document.getElementById('addNewUserForm');

    // user form validation
    if (addNewUserForm) {
        const fv = FormValidation.formValidation(addNewUserForm, {
            fields: {
                name: {
                    validators: {
                        notEmpty: {
                            message: 'Por favor preencha o nome completo'
                        }
                    }
                },
                email: {
                    validators: {
                        notEmpty: {
                            message: 'Por favor preencha o email'
                        },
                        emailAddress: {
                            message: 'O email nao é valido'
                        }
                    }
                },
                userContact: {
                    validators: {
                        notEmpty: {
                            message: 'Por favor insira seu número'
                        }
                    }
                },
                company: {
                    validators: {
                        notEmpty: {
                            message: 'Please enter your company'
                        }
                    }
                }
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    // Use this for enabling/changing valid/invalid class
                    eleValidClass: '',
                    rowSelector: function (field, ele) {
                        // field is the field name & ele is the field element
                        return '.mb-6';
                    }
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus()
            }
        }).on('core.form.valid', function () {
            // adding or updating user when form successfully validate
            const formData = new FormData(addNewUserForm);
            const formDataObj = {};

            // Convert FormData to URL-encoded string
            formData.forEach((value, key) => {
                formDataObj[key] = value;
            });

            const searchParams = new URLSearchParams();
            for (const [key, value] of Object.entries(formDataObj)) {
                searchParams.append(key, value);
            }

            fetch(`${baseUrl}user-list`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: searchParams.toString()
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(status => {
                    // Refresh DataTable
                    dt_user_table && new DataTable(dt_user_table).draw();

                    // Hide offcanvas
                    const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasForm);
                    offcanvasInstance && offcanvasInstance.hide();

                    // sweetalert
                    Swal.fire({
                        icon: 'success',
                        title: `${status} com sucesso!`,
                        text: `Usuário ${status} com sucesso.`,
                        customClass: {
                            confirmButton: 'btn btn-success'
                        }
                    });
                })
                .catch(err => {
                    // Hide offcanvas
                    const offcanvasInstance = bootstrap.Offcanvas.getInstance(offCanvasForm);
                    offcanvasInstance && offcanvasInstance.hide();

                    Swal.fire({
                        title: 'Duplicate Entry!',
                        text: 'Your email should be unique.',
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn btn-success'
                        }
                    });
                });
        });

        // clearing form data when offcanvas hidden
        offCanvasForm.addEventListener('hidden.bs.offcanvas', function () {
            fv.resetForm(true);
        });
    }

    // Phone mask initialization
    const phoneMaskList = document.querySelectorAll('.phone-mask');

    // Phone Number
    function formatBRPhone(digits) {
        // só números e no máximo 11
        digits = (digits || '').replace(/\D/g, '').slice(0, 11);

        const ddd = digits.slice(0, 2);
        const part1 = digits.slice(2);

        // enquanto digita, vai montando aos poucos
        let out = '';
        if (ddd.length) out = `(${ddd}`;
        if (ddd.length === 2) out += ') ';

        // 10 dígitos total: (DD) 0000-0000  => part1: 8
        // 11 dígitos total: (DD) 00000-0000 => part1: 9
        if (part1.length) {
            const isMobile = digits.length > 10; // 11 dígitos
            const firstLen = isMobile ? 5 : 4;

            const a = part1.slice(0, firstLen);
            const b = part1.slice(firstLen, firstLen + 4);

            out += a;
            if (b.length) out += `-${b}`;
        }

        return out;
    }

    if (phoneMaskList && phoneMaskList.length) {
        phoneMaskList.forEach(phoneMask => {
            phoneMask.addEventListener('input', event => {
                event.target.value = formatBRPhone(event.target.value);
            });


        });
    }
});
