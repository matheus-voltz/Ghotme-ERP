@extends('layouts/layoutMaster')

@section('title', 'Editar Modelo: ' . $template->name)

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-6">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Editor de Template (HTML)</h5>
            </div>
            <div class="card-body pt-6">
                <form id="formEditTemplate">
                    @csrf
                    <div class="mb-6">
                        <label class="form-label">Estrutura HTML</label>
                        <textarea name="content" class="form-control" rows="20" style="font-family: monospace; font-size: 13px;">{{ $template->content }}</textarea>
                    </div>
                    <div class="mb-6">
                        <label class="form-label">Estilo CSS</label>
                        <textarea name="css" class="form-control" rows="10" style="font-family: monospace; font-size: 13px;">{{ $template->css }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    <a href="{{ route('settings.print-templates') }}" class="btn btn-label-secondary">Voltar</a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-6">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Tags Disponíveis</h5>
            </div>
            <div class="card-body pt-6">
                <p>Use as tags abaixo entre <code>{{ }}</code> para que o sistema preencha os dados automaticamente.</p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Empresa <span><code>{{company_name}}</code>, <code>{{company_cnpj}}</code></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Cliente <span><code>{{client_name}}</code>, <code>{{client_phone}}</code></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Veículo <span><code>{{vehicle_plate}}</code>, <code>{{vehicle_model}}</code></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        OS <span><code>{{os_number}}</code>, <code>{{os_total}}</code></span>
                    </li>
                </ul>
                <div class="alert alert-warning mt-4 p-2">
                    <small><i class="ti tabler-alert-triangle me-1"></i> <strong>Atenção:</strong> Alterar a estrutura do <code>items_loop</code> pode quebrar a listagem de peças/serviços.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('formEditTemplate').onsubmit = function(e) {
        e.preventDefault();
        const formData = new URLSearchParams(new FormData(this));
        fetch("{{ route('settings.print-templates.update', $template->id) }}", {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        }).then(res => res.json()).then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'Sucesso!', text: data.message, customClass: { confirmButton: 'btn btn-success' } });
            }
        });
    };
});
</script>
@endsection
