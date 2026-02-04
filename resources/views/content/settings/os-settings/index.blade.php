@extends('layouts/layoutMaster')

@section('title', 'Configurações da OS')

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
                <h5 class="card-title mb-0">Parâmetros de Ordem de Serviço e Orçamentos</h5>
            </div>
            <div class="card-body pt-6">
                <form id="formAppSettings">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Prefixo da OS</label>
                            <input type="text" name="os_prefix" class="form-control" value="{{ $settings->os_prefix }}" placeholder="Ex: OS-" />
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label">Próximo Número</label>
                            <input type="number" name="os_next_number" class="form-control" value="{{ $settings->os_next_number }}" />
                        </div>
                        <div class="col-md-12 mb-4">
                            <label class="form-label">Validade Padrão de Orçamentos (dias)</label>
                            <input type="number" name="budget_validity_days" class="form-control" value="{{ $settings->budget_validity_days }}" />
                        </div>
                        <div class="col-md-12 mb-4">
                            <label class="form-label">Termos de Garantia e Condições (Rodapé da OS)</label>
                            <textarea name="os_terms" class="form-control" rows="6">{{ $settings->os_terms }}</textarea>
                            <small class="text-muted">Este texto aparecerá no final de todas as ordens de serviço impressas.</small>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('formAppSettings').onsubmit = function(e) {
        e.preventDefault();
        fetch("{{ route('settings.os-settings.update') }}", {
            method: 'POST',
            body: new URLSearchParams(new FormData(this)),
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
