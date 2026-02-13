@extends('layouts/layoutMaster')

@section('title', 'Gerenciar Equipe')

<!-- Vendor Styles -->
@section('vendor-style')
@vite(['resources/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.scss',
'resources/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.scss',
'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/@form-validation/form-validation.scss',
'resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

<!-- Vendor Scripts -->
@section('vendor-script')
@vite(['resources/assets/vendor/libs/moment/moment.js',
'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/cleave-zen/cleave-zen.js',
'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

<!-- Page Scripts -->
@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Phone Mask
        var phoneMaskList = document.querySelectorAll('.phone-mask');
        phoneMaskList.forEach(function(phoneMask) {
            phoneMask.addEventListener('input', function(event) {
                var value = event.target.value.replace(/\D/g, '');
                if (value.length > 11) value = value.slice(0, 11);

                var formatted = '';
                if (value.length > 0) {
                    formatted = '(' + value.slice(0, 2);
                }
                if (value.length > 2) {
                    formatted += ') ' + value.slice(2, 7);
                }
                if (value.length > 7) {
                    formatted += '-' + value.slice(7, 11);
                }
                event.target.value = formatted;
            });
        });

        var dt_user_table = $('.datatables-users');
        var table;

        if (dt_user_table.length) {
            table = dt_user_table.DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('team-management.data') }}",
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },
                columns: [{
                        data: 'fake_id',
                        name: 'id'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'role',
                        name: 'role'
                    },
                    {
                        data: 'email_verified_at',
                        name: 'email_verified_at'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="d-inline-block text-nowrap">
                                    <button class="btn btn-sm btn-icon edit-record" data-id="${row.id}" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser"><i class="ti tabler-edit"></i></button>
                                    <button class="btn btn-sm btn-icon delete-record" data-id="${row.id}"><i class="ti tabler-trash"></i></button>
                                </div>
                            `;
                        }
                    }
                ],
                dom: '<"row mx-1"' +
                    '<"col-md-2 d-flex align-items-center justify-content-md-start justify-content-center ps-4"l>' +
                    '<"col-md-10 d-flex align-items-center justify-content-md-end justify-content-center pe-4"f>' +
                    '>t' +
                    '<"row mx-2"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>' +
                    '>',
                language: {
                    sLengthMenu: 'Mostrar _MENU_',
                    search: 'Pesquisar',
                    searchPlaceholder: 'Pesquisar...',
                    emptyTable: "Nenhum dado disponível na tabela"
                }
            });
        }

        // Edit Record
        $(document).on('click', '.edit-record', function() {
            var user_id = $(this).data('id');
            $('#offcanvasAddUserLabel').html('Editar Funcionário');

            $.get("{{ url('settings/team') }}/" + user_id + "/edit", function(data) {
                $('#user_id').val(data.id);
                $('#add-user-fullname').val(data.name);
                $('#add-user-email').val(data.email);
                $('#add-user-contact').val(data.contact_number);
                $('#user-role').val(data.role || 'subscriber');
            });
        });

        // Delete Record
        $(document).on('click', '.delete-record', function() {
            var user_id = $(this).data('id');
            Swal.fire({
                title: 'Você tem certeza?',
                text: "Por favor, informe o motivo da exclusão:",
                input: 'text',
                inputAttributes: {
                    autocapitalize: 'off',
                    placeholder: 'Ex: Saiu da empresa, Duplicado...'
                },
                showCancelButton: true,
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                customClass: {
                    confirmButton: 'btn btn-danger me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false,
                preConfirm: (reason) => {
                    if (!reason) {
                        Swal.showValidationMessage('O motivo é obrigatório');
                    }
                    return reason;
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'DELETE',
                        url: "{{ url('settings/team') }}/" + user_id,
                        data: {
                            reason: result.value,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (table) table.ajax.reload();
                            Swal.fire({
                                icon: 'success',
                                title: 'Excluído!',
                                text: response.message || 'Funcionário removido com sucesso.',
                                customClass: {
                                    confirmButton: 'btn btn-success'
                                }
                            });
                        },
                        error: function(xhr) {
                            var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Erro ao deletar';
                            Swal.fire({
                                title: 'Erro!',
                                text: msg,
                                icon: 'error',
                                customClass: {
                                    confirmButton: 'btn btn-primary'
                                }
                            });
                        }
                    });
                }
            });
        });

        // Form Submit
        $('#addNewUserForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            $.ajax({
                data: formData,
                url: "{{ route('team-management.store') }}",
                type: "POST",
                success: function(data) {
                    $('#offcanvasAddUser').offcanvas('hide');
                    if (table) table.ajax.reload();
                    else window.location.reload();

                    $('#addNewUserForm')[0].reset();
                    $('#user_id').val('');
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: 'Operação realizada com sucesso.'
                    });
                },
                error: function(data) {
                    var msg = data.responseJSON && data.responseJSON.message ? data.responseJSON.message : 'Erro ao salvar';
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: msg
                    });
                }
            });
        });
    });
</script>
@endsection

@section('content')

<!-- Stats -->
<div class="row g-6 mb-6">
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div class="content-left">
                        <span class="text-heading">Total Equipe</span>
                        <div class="d-flex align-items-center my-1">
                            <h4 class="mb-0 me-2">{{ $totalUser }}</h4>
                        </div>
                    </div>
                    <div class="avatar">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="ti tabler-users icon-26px"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users List Table -->
<div class="card">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Membros da Equipe</h5>
        <button class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddUser">
            <i class="ti tabler-plus me-0 me-sm-1"></i>
            <span class="d-none d-sm-inline-block">Adicionar Usuário</span>
        </button>
    </div>
    <div class="card-datatable table-responsive">
        <table class="datatables-users table border-top">
            <thead>
                <tr>
                    <th>Id</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Permissão</th>
                    <th>Verificado</th>
                    <th>Ações</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Offcanvas to add new user -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
        <div class="offcanvas-header border-bottom">
            <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Adicionar Funcionário</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
            <form class="add-new-user pt-0" id="addNewUserForm">
                @csrf
                <input type="hidden" name="id" id="user_id">
                <div class="mb-6">
                    <label class="form-label" for="add-user-fullname">Nome Completo</label>
                    <input type="text" class="form-control" id="add-user-fullname" placeholder="Nome Sobrenome" name="name" required />
                </div>
                <div class="mb-6">
                    <label class="form-label" for="add-user-email">Email</label>
                    <input type="email" id="add-user-email" class="form-control" placeholder="email@exemplo.com" name="email" required />
                </div>
                <div class="mb-6">
                    <label class="form-label" for="add-user-contact">Numero Contato</label>
                    <input type="text" id="add-user-contact" class="form-control phone-mask" placeholder="(11) 99999-9999" name="userContact" />
                </div>
                <div class="mb-6">
                    <label class="form-label" for="user-role">Permissão</label>
                    <select id="user-role" class="form-select" name="role">
                        <option value="subscriber">Padrão</option>
                        <option value="editor">Editor</option>
                        <option value="admin">Administrador (Limitado)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary me-3 data-submit">Salvar</button>
                <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>
            </form>
        </div>
    </div>
</div>
@endsection