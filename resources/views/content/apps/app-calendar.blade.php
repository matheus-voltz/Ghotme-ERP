@extends('layouts/layoutMaster')

@section('title', 'Calendário - Apps')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/fullcalendar/fullcalendar.scss',
'resources/assets/vendor/libs/flatpickr/flatpickr.scss', 'resources/assets/vendor/libs/select2/select2.scss',
'resources/assets/vendor/libs/quill/editor.scss', 'resources/assets/vendor/libs/@form-validation/form-validation.scss'])
@endsection

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/app-calendar.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/fullcalendar/fullcalendar.js',
'resources/assets/vendor/libs/@form-validation/popular.js',
'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
'resources/assets/vendor/libs/@form-validation/auto-focus.js', 'resources/assets/vendor/libs/select2/select2.js',
'resources/assets/vendor/libs/flatpickr/flatpickr.js', 'resources/assets/vendor/libs/moment/moment.js'])
@endsection

@section('page-script')
@vite(['resources/assets/js/app-calendar-events.js', 'resources/assets/js/app-calendar.js'])
@endsection

@section('content')
<div class="card app-calendar-wrapper">
  <div class="row g-0">
    <!-- Calendar Sidebar -->
    <div class="col app-calendar-sidebar border-end" id="app-calendar-sidebar">
      <div class="border-bottom p-6 my-sm-0 mb-4">
        <button class="btn btn-primary btn-toggle-sidebar w-100" data-bs-toggle="offcanvas"
          data-bs-target="#addEventSidebar" aria-controls="addEventSidebar">
          <i class="icon-base ti tabler-plus icon-16px me-2"></i>
          <span class="align-middle">Adicionar Evento</span>
        </button>
      </div>
      <div class="px-3 pt-2">
        <!-- inline calendar (flatpicker) -->
        <div class="inline-calendar"></div>
      </div>
      <hr class="mb-6 mx-n4 mt-3" />
      <div class="px-6 pb-2">
        <!-- Filter -->
        <div>
          <h5>Filtros de Eventos</h5>
        </div>

        <div class="form-check form-check-secondary mb-5 ms-2">
          <input class="form-check-input select-all" type="checkbox" id="selectAll" data-value="all" checked />
          <label class="form-check-label" for="selectAll">Ver Todos</label>
        </div>

        <div class="app-calendar-events-filter text-heading">
          <div class="form-check form-check-danger mb-5 ms-2">
            <input class="form-check-input input-filter" type="checkbox" id="select-personal" data-value="personal"
              checked />
            <label class="form-check-label" for="select-personal">Pessoal</label>
          </div>
          <div class="form-check mb-5 ms-2">
            <input class="form-check-input input-filter" type="checkbox" id="select-business" data-value="business"
              checked />
            <label class="form-check-label" for="select-business">Negócios</label>
          </div>
          <div class="form-check form-check-warning mb-5 ms-2">
            <input class="form-check-input input-filter" type="checkbox" id="select-family" data-value="family"
              checked />
            <label class="form-check-label" for="select-family">Família</label>
          </div>
          <div class="form-check form-check-success mb-5 ms-2">
            <input class="form-check-input input-filter" type="checkbox" id="select-holiday" data-value="holiday"
              checked />
            <label class="form-check-label" for="select-holiday">Feriado</label>
          </div>
          <div class="form-check form-check-info ms-2">
            <input class="form-check-input input-filter" type="checkbox" id="select-etc" data-value="etc" checked />
            <label class="form-check-label" for="select-etc">ETC</label>
          </div>
        </div>
      </div>
    </div>
    <!-- /Calendar Sidebar -->

    <!-- Calendar & Modal -->
    <div class="col app-calendar-content">
      <div class="card shadow-none border-0">
        <div class="card-body pb-0">
          <!-- FullCalendar -->
          <div id="calendar"></div>
        </div>
      </div>
      <div class="app-overlay"></div>
      <!-- FullCalendar Offcanvas -->
      <div class="offcanvas offcanvas-end event-sidebar" tabindex="-1" id="addEventSidebar"
        aria-labelledby="addEventSidebarLabel">
        <div class="offcanvas-header border-bottom">
          <h5 class="offcanvas-title" id="addEventSidebarLabel">Adicionar Evento</h5>
          <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
          <form class="event-form pt-0" id="eventForm" onsubmit="return false">
            <div class="mb-5 form-control-validation">
              <label class="form-label" for="eventTitle">Título</label>
              <input type="text" class="form-control" id="eventTitle" name="eventTitle" placeholder="Título do Evento" />
            </div>
            <div class="mb-5">
              <label class="form-label" for="eventLabel">Etiqueta</label>
              <select class="select2 select-event-label form-select" id="eventLabel" name="eventLabel">
                <option data-label="primary" value="Business" selected>Negócios</option>
                <option data-label="danger" value="Personal">Pessoal</option>
                <option data-label="warning" value="Family">Família</option>
                <option data-label="success" value="Holiday">Feriado</option>
                <option data-label="info" value="ETC">ETC</option>
              </select>
            </div>
            <div class="mb-5 form-control-validation">
              <label class="form-label" for="eventStartDate">Data de Início</label>
              <input type="text" class="form-control" id="eventStartDate" name="eventStartDate"
                placeholder="Data de Início" />
            </div>
            <div class="mb-5 form-control-validation">
              <label class="form-label" for="eventEndDate">Data de Término</label>
              <input type="text" class="form-control" id="eventEndDate" name="eventEndDate" placeholder="Data de Término" />
            </div>
            <div class="mb-5">
              <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input allDay-switch" id="allDaySwitch" />
                <label class="form-check-label" for="allDaySwitch">Dia Inteiro</label>
              </div>
            </div>
            <div class="mb-5">
              <label class="form-label" for="eventURL">URL do Evento</label>
              <input type="url" class="form-control" id="eventURL" name="eventURL"
                placeholder="https://www.google.com" />
            </div>
            <div class="mb-4 select2-primary">
              <label class="form-label" for="eventGuests">Adicionar Convidados</label>
              <select class="select2 select-event-guests form-select" id="eventGuests" name="eventGuests" multiple>
                <option data-avatar="1.png" value="Jane Foster">Jane Foster</option>
                <option data-avatar="3.png" value="Donna Frank">Donna Frank</option>
                <option data-avatar="5.png" value="Gabrielle Robertson">Gabrielle Robertson</option>
                <option data-avatar="7.png" value="Lori Spears">Lori Spears</option>
                <option data-avatar="9.png" value="Sandy Vega">Sandy Vega</option>
                <option data-avatar="11.png" value="Cheryl May">Cheryl May</option>
              </select>
            </div>
            <div class="mb-5">
              <label class="form-label" for="eventLocation">Localização</label>
              <input type="text" class="form-control" id="eventLocation" name="eventLocation"
                placeholder="Digite a Localização" />
            </div>
            <div class="mb-5">
              <label class="form-label" for="eventDescription">Descrição</label>
              <textarea class="form-control" name="eventDescription" id="eventDescription"></textarea>
            </div>
            <div class="d-flex justify-content-sm-between justify-content-start mt-6 gap-2">
              <div class="d-flex">
                <button type="submit" id="addEventBtn" class="btn btn-primary btn-add-event me-4">Adicionar</button>
                <button type="reset" class="btn btn-label-secondary btn-cancel me-sm-0 me-1"
                  data-bs-dismiss="offcanvas">Cancelar</button>
              </div>
              <button class="btn btn-label-danger btn-delete-event d-none">Excluir</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- /Calendar & Modal -->
  </div>
</div>
@endsection
