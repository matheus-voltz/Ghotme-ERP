@extends('layouts/layoutMaster')

@section('title', 'Histórico do Veículo')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/animate-css/animate.scss'
])
<style>
.timeline {
    position: relative;
    padding: 20px 0;
}
.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 30px;
    border-left: 2px solid #e9ecef;
}
.timeline-item:last-child {
    border-left: 2px solid transparent;
}
.timeline-point {
    position: absolute;
    left: -9px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #7367f0;
    border: 3px solid #fff;
}
.timeline-date {
    font-size: 0.85rem;
    color: #a1acb8;
    margin-bottom: 5px;
}
.timeline-title {
    font-weight: 600;
    margin-bottom: 5px;
}
</style>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/moment/moment.js'
])
@endsection

@section('page-script')
@vite(['resources/js/vehicle-history.js'])
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-6">
            <div class="card-body">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label" for="vehicle-search">Buscar {{ niche('entity') }} ({{ niche('identifier') }} ou Chassi)</label>
                        <select id="vehicle-search" class="select2 form-select"></select>
                    </div>
                    <div class="col-md-4 mt-md-0 mt-4">
                        <button class="btn btn-primary w-100" id="btn-add-history" disabled data-bs-toggle="modal" data-bs-target="#modalAddHistory">
                            <i class="ti tabler-plus me-1"></i> Adicionar Registro Manual
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalhes do Veículo -->
    <div class="col-md-4 d-none" id="vehicle-info-card">
        <div class="card mb-6">
            <div class="card-body">
                <div class="user-avatar-section mb-6">
                    <div class="d-flex align-items-center flex-column">
                        <div class="bg-label-primary p-4 rounded mb-4">
                            <i class="ti {{ niche_config('icons.entity') }} icon-32px"></i>
                        </div>
                        <div class="user-info text-center">
                            <h4 id="info-plate">---</h4>
                            <span class="badge bg-label-secondary" id="info-brand-model">---</span>
                        </div>
                    </div>
                </div>
                <h5 class="pb-4 border-bottom mb-4">Detalhes</h5>
                <div class="info-container">
                    <ul class="list-unstyled mb-6">
                        <li class="mb-2"><span class="fw-medium me-2">Dono:</span> <span id="info-owner">---</span></li>
                        <li class="mb-2"><span class="fw-medium me-2">{{ niche('metric') }} Atual:</span> <span id="info-km">---</span></li>
                        <li class="mb-2"><span class="fw-medium me-2">Chassi:</span> <span id="info-chassis" class="text-break">---</span></li>
                        <li class="mb-2"><span class="fw-medium me-2">{{ niche('color') }}:</span> <span id="info-color">---</span></li>
                        <li class="mb-2"><span class="fw-medium me-2">{{ niche('year') }}:</span> <span id="info-year">---</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline de Histórico -->
    <div class="col-md-8 d-none" id="timeline-card">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Linha do Tempo de Manutenções</h5>
            </div>
            <div class="card-body">
                <div id="vehicle-timeline" class="timeline">
                    <!-- Dinâmico via JS -->
                    <p class="text-center text-muted py-10">Selecione um {{ strtolower(niche('entity')) }} para ver o histórico.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Adicionar Histórico -->
<div class="modal fade" id="modalAddHistory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4">
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Novo Registro de Histórico</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAddHistory">
                @csrf
                <input type="hidden" name="veiculo_id" id="modal-vehicle-id">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">Título do Evento</label>
                        <input type="text" name="title" class="form-control" placeholder="Ex: Troca de Óleo / Pastilhas" required>
                    </div>
                    <div class="row mb-4">
                        <div class="col-6">
                            <label class="form-label">Data</label>
                            <input type="text" name="date" class="form-control flatpickr" placeholder="YYYY-MM-DD" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">{{ niche('metric') }} no momento</label>
                            <input type="number" name="km" class="form-control" placeholder="0" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Tipo de Registro</label>
                        <select name="event_type" class="form-select">
                            <option value="manutencao_externa">Manutenção Externa (Outra oficina)</option>
                            <option value="observacao">Observação / Nota</option>
                            <option value="troca_proprietario">Troca de Proprietário</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Quem realizou? (Opcional)</label>
                        <input type="text" name="performer" class="form-control" placeholder="Oficina X, Dono anterior, etc">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Descrição / Observações</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer pb-0">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
