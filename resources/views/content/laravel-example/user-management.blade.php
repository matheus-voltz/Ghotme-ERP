@extends('layouts/layoutMaster')

@section('title', 'User Management - Crud App')

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
@vite(['resources/js/laravel-user-management.js'])
@endsection

@section('content')
{{-- <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add User</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0">
    <form class="add-new-user pt-0" id="addNewUserForm">
      ...
      ...
    </form>
  </div>
</div> --}}
<div class="row g-6 mb-6">
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Users</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $totalUser }}</h4>
              <p class="text-success mb-0">(100%)</p>
            </div>
            <small class="mb-0">Total Users</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-primary">
              <i class="icon-base ti tabler-users icon-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Verified Users</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $verified }}</h4>
              <p class="text-success mb-0">(+95%)</p>
            </div>
            <small class="mb-0">Recent analytics </small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-danger">
              <i class="icon-base ti tabler-user-plus icon-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Duplicate Users</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $userDuplicates }}</h4>
              <p class="text-success mb-0">(0%)</p>
            </div>
            <small class="mb-0">Recent analytics</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-success">
              <i class="icon-base ti tabler-user-check icon-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div class="content-left">
            <span class="text-heading">Verification Pending</span>
            <div class="d-flex align-items-center my-1">
              <h4 class="mb-0 me-2">{{ $notVerified }}</h4>
              <p class="text-danger mb-0">(+6%)</p>
            </div>
            <small class="mb-0">Recent analytics</small>
          </div>
          <div class="avatar">
            <span class="avatar-initial rounded bg-label-warning">
              <i class="icon-base ti tabler-user-search icon-26px"></i>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Users List Table -->
<div class="card">
  <div class="card-header border-bottom">
    <h5 class="card-title mb-0">Search Filter</h5>
  </div>
  <div class="card-datatable">
    <table class="datatables-users table border-top">
      <thead>
        <tr>
          <th></th>
          <th>Id</th>
          <th>User</th>
          <th>Email</th>
          <th>Verified</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
  </div>
  <!-- Offcanvas to add new user -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddUserLabel" class="offcanvas-title">Add User</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
      <form class="add-new-user pt-0" id="addNewUserForm">
        @csrf
        <input type="hidden" name="id" id="user_id">
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-user-fullname">Nome Completo</label>
          <input type="text" class="form-control" id="add-user-fullname" placeholder="John Doe" name="name"
            aria-label="John Doe"/>
        </div>
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-user-email">Email</label>
          <input type="text" id="add-user-email" class="form-control" placeholder="john.doe@example.com"
            aria-label="john.doe@example.com" name="email" />
        </div>
        <div class="mb-6">
          <label class="form-label" for="add-user-contact">Numero contato</label>
          <input type="text" id="add-user-contact" class="form-control phone-mask" placeholder="(11) 91234-5678"
            aria-label="john.doe@example.com" name="userContact" />
        </div>
        <div class="mb-6">
          <label class="form-label" for="add-user-company">Empresa</label>
          <input type="text" id="add-user-company" class="form-control" placeholder="Web Developer" aria-label="jdoe1"
            name="company" value/>
        </div>
        <div class="mb-6">
          <label class="form-label" for="country">País</label>
          <select id="country" class="select2 form-select">
            <option value="">Selecione</option>
            <option value="Australia">Austrália</option>
            <option value="Bangladesh">Bangladesh</option>
            <option value="Belarus">Bielorrússia</option>
            <option value="Brazil">Brasil</option>
            <option value="Canada">Canadá</option>
            <option value="China">China</option>
            <option value="France">França</option>
            <option value="Germany">Alemanha</option>
            <option value="India">Índia</option>
            <option value="Indonesia">Indonésia</option>
            <option value="Israel">Israel</option>
            <option value="Italy">Itália</option>
            <option value="Japan">Japão</option>
            <option value="Korea">Coreia, República da</option>
            <option value="Mexico">éxico</option>
            <option value="Philippines">Filipinas</option>
            <option value="Russia">Federação Russa</option>
            <option value="South Africa">África do Sul</option>
            <option value="Thailand">Tailândia</option>
            <option value="Turkey">Turquia</option>
            <option value="Ukraine">Ucrânia</option>
            <option value="United Arab Emirates">Emirados Árabes Unidos</option>
            <option value="United Kingdom">Reino Unido</option>
            <option value="United States">Estados Unidos</option>
          </select>
        </div>
        <div class="mb-6">
          <label class="form-label" for="user-role">Permissão</label>
          <select id="user-role" class="form-select">
            <option value="subscriber">Inscrito</option>
            <option value="editor">Editor</option>
            <option value="maintainer">Mantenedor</option>
            <option value="author">Autor</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
        <div class="mb-6">
          <label class="form-label" for="user-plan">Plano</label>
          <select id="user-plan" class="form-select">
            <option value="basic">Básico</option>
            <option value="enterprise">Empresarial</option>
            <option value="company">Companhia</option>
            <option value="team">Equipe</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary me-3 data-submit">Enviar</button>
        <button type="reset" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>
      </form>
    </div>
  </div>
</div>
{{-- // ...existing code... --}}
<script>
  $(function () {
    var table = $('.datatables-users').DataTable({
      processing: true,
      serverSide: true,
      // Atualize aqui para usar a rota 'user-list'
      ajax: "{{ route('user-list') }}",
      columns: [
        { data: 'fake_id', name: 'id' },
        { data: 'name', name: 'name' },
        { data: 'email', name: 'email' },
        { data: 'email_verified_at', name: 'email_verified_at' },
        { data: 'action', name: 'action', orderable: false, searchable: false }
      ],
      // ...existing code...
    });
  });

    // edit record
  $(document).on('click', '.edit-record', function () {
    var user_id = $(this).data('id');
    console.log('Editing user with ID:', user_id);
    // changing the title of offcanvas
    $('#offcanvasAddUserLabel').html('Edit User');

    // get data
    $.get(`${baseUrl}user-list\/${user_id}\/edit`, function (data) {
      $('#user_id').val(data.id);
      $('#add-user-fullname').val(data.name);
      $('#add-user-email').val(data.email);
      $('#add-user-contact').val(data.contact_number || '');
      $('#add-user-company').val(data.company || '');

      $('#country').val(data.country).trigger('change'); // bom para select2
      $('#user-role').val(data.role).trigger('change');
      $('#user-plan').val(data.plan).trigger('change');
    });

  });
</script>
@endsection

