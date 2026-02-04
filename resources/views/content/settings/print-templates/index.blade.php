@extends('layouts/layoutMaster')

@section('title', 'Modelos de Impressão')

@section('content')
<div class="row">
    @foreach($templates as $template)
    <div class="col-md-4">
        <div class="card mb-6">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="avatar bg-label-primary p-2 me-3">
                        <i class="ti tabler-file-text ti-md"></i>
                    </div>
                    <h5 class="mb-0">{{ $template->name }}</h5>
                </div>
                <p class="text-muted small">Identificador único: <code>{{ $template->slug }}</code></p>
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('settings.print-templates.edit', $template->id) }}" class="btn btn-primary w-100">
                        <i class="ti tabler-edit me-1"></i> Editar Layout
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
