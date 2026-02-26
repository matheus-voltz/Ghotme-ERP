@extends('layouts/layoutMaster')

@section('title', 'Novidades e Atualizações - Ghotme')

@section('content')
<div class="row">
  <div class="col-12">
    <!-- Header com fundo forçado escuro para garantir leitura do texto branco -->
    <div class="card mb-6 shadow-sm border-0 overflow-hidden" style="background: linear-gradient(72.47deg, #7367f0 22.16%, #4844a3 76.47%); border-radius: 1rem;">
      <div class="card-body p-8 text-center position-relative">
        <h3 class="text-white fw-extrabold mb-2 position-relative z-1" style="color: white !important;">Central de Evolução Ghotme</h3>
        <p class="text-white opacity-90 mb-0 position-relative z-1" style="color: rgba(255,255,255,0.9) !important;">Acompanhe as melhorias e novas ferramentas que implementamos para o seu negócio.</p>
        <!-- Decorativo -->
        <div class="position-absolute top-0 end-0 p-4 opacity-25">
            <i class="ti tabler-rocket text-white" style="font-size: 4rem; color: white !important;"></i>
        </div>
      </div>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body pt-8">
        <ul class="timeline pb-0 mb-0">
          @forelse($updates as $update)
          <li class="timeline-item timeline-item-transparent border-left-dashed">
            <span class="timeline-point timeline-point-{{ $update->type == 'feature' ? 'success' : ($update->type == 'improvement' ? 'info' : 'warning') }}"></span>
            <div class="timeline-event">
              <div class="timeline-header mb-3">
                <h6 class="mb-0 fw-bold text-heading">{{ $update->title }}</h6>
                <small class="text-muted">{{ $update->created_at->translatedFormat('d \d\e F, Y') }}</small>
              </div>
              <p class="mb-3 text-body">
                {{ $update->description }}
              </p>
              <div class="d-flex align-items-center">
                <span class="badge bg-label-{{ $update->type == 'feature' ? 'success' : ($update->type == 'improvement' ? 'info' : 'warning') }} rounded-pill">
                  {{ $update->type == 'feature' ? 'Nova Funcionalidade' : ($update->type == 'improvement' ? 'Melhoria' : 'Correção') }}
                </span>
              </div>
            </div>
          </li>
          @empty
          <div class="text-center py-5">
            <i class="ti tabler-news fs-1 text-muted mb-2"></i>
            <p class="text-muted">Nenhuma atualização registrada ainda.</p>
          </div>
          @endforelse
        </ul>
        
        <div class="mt-6">
            {{ $updates->links() }}
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
