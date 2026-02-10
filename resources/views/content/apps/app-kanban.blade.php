@extends('layouts/layoutMaster')

@section('title', 'Kanban - Apps')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/jkanban/jkanban.scss', 'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/quill/typography.scss',
'resources/assets/vendor/libs/quill/katex.scss', 'resources/assets/vendor/libs/quill/editor.scss'])
@endsection

@section('page-style')
@vite('resources/assets/vendor/scss/pages/app-kanban.scss')
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/moment/moment.js', 'resources/assets/vendor/libs/flatpickr/flatpickr.js',
'resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/jkanban/jkanban.js',
'resources/assets/vendor/libs/quill/katex.js', 'resources/assets/vendor/libs/quill/quill.js'])
@endsection

@section('page-script')
@vite('resources/assets/js/app-kanban.js')
@endsection

@section('content')
<div class="app-kanban">

  <!-- Add new board -->
  <div class="row">
    <div class="col-12">
      <form class="kanban-add-new-board">
        <label class="kanban-add-board-btn" for="kanban-add-board-input">
          <i class="icon-base ti tabler-plus"></i>
          <span class="align-middle">Adicionar Quadro</span>
        </label>
        <input type="text" class="form-control w-px-250 kanban-add-board-input mb-4 d-none"
          placeholder="Título do Quadro" id="kanban-add-board-input" required />
        <div class="mb-4 kanban-add-board-input d-none">
          <button class="btn btn-primary btn-sm me-4">Adicionar</button>
          <button type="button" class="btn btn-label-secondary btn-sm kanban-add-board-cancel-btn">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Kanban Wrapper -->
  <div class="kanban-wrapper"></div>

  <!-- Edit Task/Task & Activities -->
  <div class="offcanvas offcanvas-end kanban-update-item-sidebar">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title">Editar Tarefa</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body pt-0">
      <div class="nav-align-top">
        <ul class="nav nav-tabs mb-5 rounded-0">
          <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-update">
              <i class="icon-base ti tabler-edit icon-18px me-1_5"></i>
              <span class="align-middle">Editar</span>
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-activity">
              <i class="icon-base ti tabler-chart-pie-2 icon-18px me-1_5"></i>
              <span class="align-middle">Atividade</span>
            </button>
          </li>
        </ul>
      </div>
      <div class="tab-content p-0">
        <!-- Update item/tasks -->
        <div class="tab-pane fade show active" id="tab-update" role="tabpanel">
          <form>
            <div class="mb-5">
              <label class="form-label" for="title">Título</label>
              <input type="text" id="title" class="form-control" placeholder="Digite o Título" />
            </div>
            <div class="mb-5">
              <label class="form-label" for="due-date">Data de Entrega</label>
              <input type="text" id="due-date" class="form-control" placeholder="Data de Entrega" />
            </div>
            <div class="mb-5">
              <label class="form-label" for="label"> Etiqueta</label>
              <select class="select2 select2-label form-select" id="label">
                <option data-color="bg-label-success" value="UX">UX</option>
                <option data-color="bg-label-warning" value="Images">Imagens</option>
                <option data-color="bg-label-info" value="Info">Info</option>
                <option data-color="bg-label-danger" value="Code Review">Code Review</option>
                <option data-color="bg-label-secondary" value="App">App</option>
                <option data-color="bg-label-primary" value="Charts & Maps">Gráficos & Mapas</option>
              </select>
            </div>
            <div class="mb-5">
              <label class="form-label">Atribuído a</label>
              <div class="assigned d-flex flex-wrap"></div>
            </div>
            <div class="mb-5">
              <label class="form-label" for="attachments">Anexos</label>
              <div>
                <input type="file" class="form-control" id="attachments" />
              </div>
            </div>
            <div class="mb-5">
              <label class="form-label">Comentário</label>
              <div class="comment-editor border-bottom-0"></div>
              <div class="d-flex justify-content-end">
                <div class="comment-toolbar">
                  <span class="ql-formats me-0">
                    <button class="ql-bold"></button>
                    <button class="ql-italic"></button>
                    <button class="ql-underline"></button>
                    <button class="ql-link"></button>
                    <button class="ql-image"></button>
                  </span>
                </div>
              </div>
            </div>
            <div>
              <div class="d-flex flex-wrap">
                <button type="button" class="btn btn-primary me-4" data-bs-dismiss="offcanvas">Atualizar</button>
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Excluir</button>
              </div>
            </div>
          </form>
        </div>
        <!-- Activities -->
        <div class="tab-pane fade text-heading" id="tab-activity" role="tabpanel">
          <div class="media mb-4 d-flex align-items-center">
            <div class="avatar me-3 flex-shrink-0">
              <span class="avatar-initial bg-label-success rounded-circle">HJ</span>
            </div>
            <div class="media-body">
              <p class="mb-0"><span>Jordan</span> Saiu do quadro.</p>
              <small class="text-body-secondary">Hoje 11:00 AM</small>
            </div>
          </div>
          <div class="media mb-4 d-flex align-items-center">
            <div class="avatar me-3 flex-shrink-0">
              <img src="{{ asset('assets/img/avatars/6.png') }}" alt="Avatar" class="rounded-circle" />
            </div>
            <div class="media-body">
              <p class="mb-0"><span>Dianna</span> mencionou <span class="text-primary">@bruce</span> em um comentário.</p>
              <small class="text-body-secondary">Hoje 10:20 AM</small>
            </div>
          </div>
          <div class="media mb-4 d-flex align-items-center">
            <div class="avatar me-3 flex-shrink-0">
              <img src="{{ asset('assets/img/avatars/2.png') }}" alt="Avatar" class="rounded-circle" />
            </div>
            <div class="media-body">
              <p class="mb-0"><span>Martian</span> moveu tarefa Gráficos & Mapas para o quadro feito.</p>
              <small class="text-body-secondary">Hoje 10:00 AM</small>
            </div>
          </div>
          <div class="media mb-4 d-flex align-items-center">
            <div class="avatar me-3 flex-shrink-0">
              <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />
            </div>
            <div class="media-body">
              <p class="mb-0"><span>Barry</span> Comentou na tarefa de revisão do App.</p>
              <small class="text-body-secondary">Hoje 8:32 AM</small>
            </div>
          </div>
          <div class="media mb-4 d-flex align-items-center">
            <div class="avatar me-3 flex-shrink-0">
              <span class="avatar-initial bg-label-dark rounded-circle">BW</span>
            </div>
            <div class="media-body">
              <p class="mb-0"><span>Bruce</span> foi designado para revisão de código.</p>
              <small class="text-body-secondary">Hoje 8:30 PM</small>
            </div>
          </div>
          <div class="media mb-4 d-flex align-items-center">
            <div class="avatar me-3 flex-shrink-0">
              <span class="avatar-initial bg-label-danger rounded-circle">CK</span>
            </div>
            <div class="media-body">
              <p class="mb-0">
                <span>Clark</span> designou tarefa UX Research para
                <span class="text-primary">@martian</span>
              </p>
              <small class="text-body-secondary">Hoje 8:00 AM</small>
            </div>
          </div>
          <div class="media mb-4 d-flex align-items-center">
            <div class="avatar me-3 flex-shrink-0">
              <img src="{{ asset('assets/img/avatars/4.png') }}" alt="Avatar" class="rounded-circle" />
            </div>
            <div class="media-body">
              <p class="mb-0"><span>Ray</span> Moveu tarefa <span>Formulários & Tabelas</span> de em progresso para feito.
              </p>
              <small class="text-body-secondary">Hoje 7:45 AM</small>
            </div>
          </div>
          <div class="media mb-4 d-flex align-items-center">
            <div class="avatar me-3 flex-shrink-0">
              <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle" />
            </div>
            <div class="media-body">
              <p class="mb-0"><span>Barry</span> Completou todas as tarefas.</p>
              <small class="text-body-secondary">Hoje 7:17 AM</small>
            </div>
          </div>
          <div class="media mb-4 d-flex align-items-center">
            <div class="avatar me-3 flex-shrink-0">
              <span class="avatar-initial bg-label-success rounded-circle">HJ</span>
            </div>
            <div class="media-body">
              <p class="mb-0"><span>Jordan</span> adicionou tarefa para atualizar imagens.</p>
              <small class="text-body-secondary">Hoje 7:00 AM</small>
            </div>
          </div>
          <div class="media mb-4 d-flex align-items-center">
            <div class="avatar me-3 flex-shrink-0">
              <img src="{{ asset('assets/img/avatars/6.png') }}" alt="Avatar" class="rounded-circle" />
            </div>
            <div class="media-body">
              <p class="mb-0"><span>Dianna</span> moveu tarefa <span>FAQ UX</span> de em progresso para feito.</p>
              <small class="text-body-secondary">Hoje 7:00 AM</small>
            </div>
          </div>
          <div class="media mb-4 d-flex align-items-center">
            <div class="avatar me-3 flex-shrink-0">
              <span class="avatar-initial bg-label-danger rounded-circle">CK</span>
            </div>
            <div class="media-body">
              <p class="mb-0"><span>Clark</span> adicionou novo quadro chamado <span>Feito</span>.</p>
              <small class="text-body-secondary">Ontem 3:00 PM</small>
            </div>
          </div>
          <div class="media d-flex align-items-center">
            <div class="avatar me-3 flex-shrink-0">
              <span class="avatar-initial bg-label-dark rounded-circle">BW</span>
            </div>
            <div class="media-body">
              <p class="mb-0"><span>Bruce</span> adicionou nova tarefa em progresso.</p>
              <small class="text-body-secondary">Ontem 12:00 PM</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection