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
              <label class="form-label" for="select2-users">Atribuído a</label>
              <select class="select2 select2-users form-select" id="select2-users" multiple>
              </select>
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
          <div class="activities-container">
            <!-- Atividades serão carregadas aqui via JS -->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection