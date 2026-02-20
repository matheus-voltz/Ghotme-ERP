@extends('layouts/layoutMaster')

@section('title', 'Kanban Financeiro')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/jkanban/jkanban.scss',
  'resources/assets/vendor/libs/select2/select2.scss',
  'resources/assets/vendor/libs/flatpickr/flatpickr.scss',
  'resources/assets/vendor/libs/quill/typography.scss',
  'resources/assets/vendor/libs/quill/katex.scss',
  'resources/assets/vendor/libs/quill/editor.scss'
])
@endsection

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/app-kanban.scss'])
<style>
    .kanban-item {
        cursor: grab;
    }
    .kanban-item:active {
        cursor: grabbing;
    }
</style>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/moment/moment.js',
  'resources/assets/vendor/libs/jkanban/jkanban.js',
  'resources/assets/vendor/libs/select2/select2.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/quill/katex.js',
  'resources/assets/vendor/libs/quill/quill.js'
])
@endsection

@section('page-script')
@vite(['resources/js/financial-kanban.js'])
@endsection

@section('content')
<div class="app-kanban">
  <!-- Kanban Wrapper -->
  <div class="kanban-wrapper"></div>
</div>
@endsection
