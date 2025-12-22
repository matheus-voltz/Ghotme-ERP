@extends('layouts/layoutMaster')

@section('title', 'Your Page Title') // change title accordingly
@section('content')
<!-- Offcanvas to add new user -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAddUser" aria-labelledby="offcanvasAddUserLabel">
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
</div>
<table class="datatables-users table">
  <thead class="table-light">
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
@endsection