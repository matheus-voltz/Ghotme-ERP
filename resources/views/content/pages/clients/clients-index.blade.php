@extends('layouts/layoutMaster')

@section('title', 'Clients')
@section('content')
@section('page-script')
@vite(['resources/js/laravel-clients.js'])
@endsection
<div class="card">
  <div class="card-header border-bottom">
  </div>
  <div class="card-datatable">
    <table class="datatables-clients table border-top">
      <thead>
        <tr>
          <th></th>
          <th>Id</th>
          <th>Tipo</th>
          <th>Nome</th>
          <th>Email</th>
          <th>Nome Empresa</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
    </table>
  </div>
  <!-- Offcanvas to add new client -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddClients" aria-labelledby="offcanvasAddClientsLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="offcanvasAddClientsLabel" class="offcanvas-title">Adicionar Cliente</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body mx-0 flex-grow-0 p-6 h-100">
      <form class="add-new-clients pt-0" id="addNewClientsForm">
        @csrf
        <input type="hidden" name="id" id="client_id">
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-client-fullname">Nome</label>
          <input type="text" class="form-control" id="add-client-fullname" placeholder="Luke Skywalker" name="name"
            aria-label="Luke Skywalker" />
        </div>
        <div class="mb-6 form-control-validation">
          <label class="form-label" for="add-client-email">Email</label>
          <input type="text" id="add-client-email" class="form-control" placeholder="john.doe@example.com"
            aria-label="john.doe@example.com" name="email" />
        </div>
        <div class="mb-6">
          <label class="form-label" for="add-client-company">Nome Empresa</label>
          <input type="text" id="add-client-company" class="form-control" placeholder="Web Developer" aria-label="jdoe1"
            name="company" value />
        </div>
        <div class="mb-6">
          <label class="form-label" for="country">País</label>
          <select id="country" class="select2 form-select" name="country">
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
          <label class="form-label" for="client-role">Permissão</label>
          <select id="client-role" class="form-select" name="role">
            <option value="subscriber">Inscrito</option>
            <option value="editor">Editor</option>
            <option value="maintainer">Mantenedor</option>
            <option value="author">Autor</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
        <div class="mb-6">
          <label class="form-label" for="client-plan">Plano</label>
          <select id="client-plan" class="form-select" name="plan">
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

@endsection