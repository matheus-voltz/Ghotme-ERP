@extends('layouts/layoutMaster')

@section('title', 'Master Control Panel - Ghotme')

@section('content')
<div class="row g-6">
  <!-- Estatísticas Globais -->
  <div class="col-lg-3 col-sm-6">
    <div class="card h-100 shadow-sm border-start border-primary" style="border-left-width: 5px !important;">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar avatar-md me-2">
            <span class="avatar-initial rounded bg-label-primary"><i class="ti tabler-building-skyscraper fs-4"></i></span>
          </div>
          <h4 class="mb-0">{{ $stats['total_companies'] }}</h4>
        </div>
        <p class="mb-0">Empresas Cadastradas</p>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-sm-6">
    <div class="card h-100 shadow-sm border-start border-success" style="border-left-width: 5px !important;">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar avatar-md me-2">
            <span class="avatar-initial rounded bg-label-success"><i class="ti tabler-users fs-4"></i></span>
          </div>
          <h4 class="mb-0">{{ $stats['total_users'] }}</h4>
        </div>
        <p class="mb-0">Usuários Totais</p>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-sm-6">
    <div class="card h-100 shadow-sm border-start border-info" style="border-left-width: 5px !important;">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar avatar-md me-2">
            <span class="avatar-initial rounded bg-label-info"><i class="ti tabler-mail-fast fs-4"></i></span>
          </div>
          <h4 class="mb-0">{{ $stats['total_subscribers'] }}</h4>
        </div>
        <p class="mb-0">Leads Newsletter</p>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-sm-6">
    <div class="card h-100 shadow-sm border-start border-warning" style="border-left-width: 5px !important;">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="avatar avatar-md me-2">
            <span class="avatar-initial rounded bg-label-warning"><i class="ti tabler-user-heart fs-4"></i></span>
          </div>
          <h4 class="mb-0">{{ $stats['total_clients'] }}</h4>
        </div>
        <p class="mb-0">Clientes dos Inquilinos</p>
      </div>
    </div>
  </div>

  <!-- Campanhas e Novidades -->
  <div class="col-md-8">
    <div class="card h-100 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center border-bottom">
        <h5 class="card-title mb-0">Gestão Global de Newsletter</h5>
        <a href="{{ route('master.newsletter.create') }}" class="btn btn-primary">Nova Campanha Global</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Assunto</th>
              <th>Data</th>
              <th>Envios</th>
            </tr>
          </thead>
          <tbody>
            @foreach($campaigns as $camp)
            <tr>
              <td><span class="fw-bold">{{ $camp->subject }}</span></td>
              <td>{{ $camp->created_at->format('d/m/Y') }}</td>
              <td><span class="badge bg-label-primary">{{ $camp->sent_count }} pessoas</span></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Changelog / System Updates -->
  <div class="col-md-4">
    <div class="card shadow-sm h-100">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Atualizar Ghotme (Changelog)</h5>
      </div>
      <div class="card-body pt-4">
        <form action="{{ route('master.system-update.store') }}" method="POST">
          @csrf
          <div class="mb-4">
            <label class="form-label">Título da Novidade</label>
            <input type="text" name="title" class="form-control" placeholder="Ex: Novo Módulo de IA" required>
          </div>
          <div class="mb-4">
            <label class="form-label">Descrição Curta</label>
            <textarea name="description" class="form-control" rows="3" placeholder="O que mudou?" required></textarea>
          </div>
          <div class="mb-4">
            <label class="form-label">Tipo</label>
            <select name="type" class="form-select">
              <option value="feature">Nova Funcionalidade</option>
              <option value="improvement">Melhoria</option>
              <option value="fix">Correção de Bug</option>
            </select>
          </div>
          <button type="submit" class="btn btn-success w-100">Salvar e Notificar IA</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Inscritos Recentes -->
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Últimos Assinantes (Landing Page)</h5>
      </div>
      <ul class="list-group list-group-flush">
        @foreach($stats['recent_subscribers'] as $sub)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          {{ $sub->email }}
          <small class="text-muted">{{ $sub->created_at->diffForHumans() }}</small>
        </li>
        @endforeach
      </ul>
    </div>
  </div>

  <!-- Empresas Recentes -->
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Últimas Empresas Cadastradas</h5>
      </div>
      <ul class="list-group list-group-flush">
        @foreach($stats['recent_companies'] as $comp)
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <div>
            <span class="fw-bold">{{ $comp->name }}</span><br>
            <small class="text-muted">{{ $comp->document_number }}</small>
          </div>
          <span class="badge bg-label-info">{{ $comp->niche }}</span>
        </li>
        @endforeach
      </ul>
    </div>
  </div>
</div>
@endsection
