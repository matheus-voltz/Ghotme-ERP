@extends('layouts/layoutMaster')

@section('title', 'Checklists de Entrada')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Histórico de Checklists</h5>
    <a href="{{ route('ordens-servico.checklist.create') }}" class="btn btn-primary">Novo Checklist</a>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Veículo</th>
          <th>Responsável</th>
          <th>Data</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach($inspections as $inspection)
        <tr>
          <td>#{{ $inspection->id }}</td>
          <td>
            <strong>{{ $inspection->veiculo->placa }}</strong><br>
            <small>{{ $inspection->veiculo->modelo }}</small>
          </td>
          <td>{{ $inspection->user->name }}</td>
          <td>{{ $inspection->created_at->format('d/m/Y H:i') }}</td>
          <td>
            <a href="{{ route('ordens-servico.checklist.show', $inspection->id) }}" class="btn btn-sm btn-icon btn-label-secondary">
              <i class="ti ti-eye"></i>
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
