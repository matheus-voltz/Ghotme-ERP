@extends('layouts/layoutMaster')

@section('title', 'Newsletter - Gerenciamento')

@section('content')
<div class="row g-6">
  <!-- Subscribers List -->
  <div class="col-md-7">
    <div class="card h-100">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Assinantes da Newsletter</h5>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>E-mail</th>
              <th>Inscrito em</th>
              <th>Status</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            @forelse($subscribers as $subscriber)
            <tr>
              <td>{{ $subscriber->email }}</td>
              <td>{{ $subscriber->created_at->format('d/m/Y H:i') }}</td>
              <td>
                <span class="badge bg-label-{{ $subscriber->is_active ? 'success' : 'danger' }}">
                  {{ $subscriber->is_active ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td>
                <form action="{{ route('newsletter.admin.subscriber.destroy', $subscriber->id) }}" method="POST" onsubmit="return confirm('Excluir este assinante?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-icon text-danger"><i class="ti tabler-trash"></i></button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="4" class="text-center">Nenhum assinante encontrado.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        {{ $subscribers->links() }}
      </div>
    </div>
  </div>

  <!-- Campaigns History -->
  <div class="col-md-5">
    <div class="card h-100">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Campanhas Enviadas</h5>
        <a href="{{ route('newsletter.admin.create') }}" class="btn btn-primary btn-sm">Nova Campanha</a>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          @forelse($campaigns as $campaign)
          <div class="list-group-item px-0">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <h6 class="mb-0 text-truncate" style="max-width: 250px;">{{ $campaign->subject }}</h6>
              <small class="text-muted">{{ $campaign->created_at->diffForHumans() }}</small>
            </div>
            <p class="small text-muted mb-0">Enviado para {{ $campaign->sent_count }} pessoas</p>
          </div>
          @empty
          <p class="text-center text-muted my-5">Nenhuma campanha enviada ainda.</p>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
