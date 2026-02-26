@extends('layouts/layoutMaster')

@section('title', 'Gestão de Inquilinos - Ghotme Master')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card shadow-sm">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Empresas e Inquilinos</h5>
        <span class="badge bg-label-primary">{{ $companies->total() }} empresas totais</span>
      </div>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Empresa</th>
              <th>Status</th>
              <th>Nicho</th>
              <th>Plano</th>
              <th>Usuários</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            @foreach($companies as $company)
            @php
                $admin = \App\Models\User::where('company_id', $company->id)->where('role', 'admin')->first();
            @endphp
            <tr>
              <td>
                <div class="d-flex flex-column">
                  <span class="fw-bold">{{ $company->name }}</span>
                  <small class="text-muted">{{ $company->document_number }}</small>
                </div>
              </td>
              <td>
                <span class="badge bg-label-{{ $company->is_active ? 'success' : 'danger' }}">
                  {{ $company->is_active ? 'Ativo' : 'Bloqueado' }}
                </span>
              </td>
              <td><span class="badge bg-label-info">{{ $company->niche ?? 'N/A' }}</span></td>
              <td>
                <span class="badge bg-label-{{ ($admin->plan ?? '') == 'enterprise' ? 'primary' : 'secondary' }}">
                  {{ strtoupper($admin->plan ?? 'PADRÃO') }}
                </span>
              </td>
              <td><i class="ti tabler-users me-1"></i> {{ $company->users_count }}</td>
              <td>
                <button class="btn btn-sm btn-icon text-primary" data-bs-toggle="modal" data-bs-target="#editCompany{{ $company->id }}"><i class="ti tabler-edit"></i></button>
              </td>
            </tr>

            <!-- Modal Editar -->
            <div class="modal fade" id="editCompany{{ $company->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <form action="{{ route('master.companies.update', $company->id) }}" method="POST">
                  @csrf
                  <div class="modal-content">
                    <div class="modal-header border-bottom">
                      <h5 class="modal-title">Configurar: {{ $company->name }}</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-4">
                        <label class="form-label">Nicho de Atuação</label>
                        <select name="niche" class="form-select">
                          @foreach(array_keys(config('niche.niches')) as $n)
                            <option value="{{ $n }}" {{ $company->niche == $n ? 'selected' : '' }}>{{ strtoupper($n) }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="mb-4">
                        <label class="form-label">Plano do Administrador</label>
                        <select name="plan" class="form-select">
                          <option value="padrao" {{ ($admin->plan ?? '') == 'padrao' ? 'selected' : '' }}>PADRÃO</option>
                          <option value="enterprise" {{ ($admin->plan ?? '') == 'enterprise' ? 'selected' : '' }}>ENTERPRISE</option>
                        </select>
                      </div>
                      <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" {{ $company->is_active ? 'checked' : '' }}>
                        <label class="form-check-label">Empresa Ativa (Acesso liberado)</label>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-primary w-100">Salvar Alterações</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        {{ $companies->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>
@endsection
