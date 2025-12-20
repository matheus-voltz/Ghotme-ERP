@extends('layouts/layoutMaster')

@section('title', 'Your Page Title') // change title accordingly
@section('content')
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