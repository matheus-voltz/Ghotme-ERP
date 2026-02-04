@extends('layouts/layoutMaster')

@section('title', 'Integrações')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
<div class="row">
    <!-- Asaas Integration -->
    <div class="col-md-6 mb-6">
        <div class="card h-100">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Integração Asaas</h5>
            </div>
            <div class="card-body pt-6">
                <form id="formAsaas">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">API Key do Asaas</label>
                        <input type="password" name="asaas_api_key" class="form-control" value="{{ $settings->asaas_api_key }}" placeholder="Digite sua chave de API" />
                        <small class="text-muted">Você encontra essa chave no painel do Asaas (Minha Conta > Integrações).</small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Ambiente</label>
                        <select name="asaas_environment" class="form-select">
                            <option value="sandbox" {{ $settings->asaas_environment == 'sandbox' ? 'selected' : '' }}>Sandbox (Testes)</option>
                            <option value="production" {{ $settings->asaas_environment == 'production' ? 'selected' : '' }}>Produção (Real)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar Asaas</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Future WhatsApp Integration -->
    <div class="col-md-6 mb-6">
        <div class="card h-100">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">Integração WhatsApp (Meta)</h5>
            </div>
            <div class="card-body pt-6">
                <form id="formWhatsApp">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label">Acess Token</label>
                        <input type="password" name="whatsapp_token" class="form-control" value="{{ $settings->whatsapp_token }}" placeholder="Meta Access Token" />
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Phone Number ID</label>
                        <input type="text" name="whatsapp_phone_number_id" class="form-control" value="{{ $settings->whatsapp_phone_number_id }}" placeholder="ID do número de telefone" />
                    </div>
                    <button type="submit" class="btn btn-primary">Salvar WhatsApp</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const handleUpdate = (formId) => {
        const form = document.getElementById(formId);
        form.onsubmit = function(e) {
            e.preventDefault();
            const formData = new URLSearchParams(new FormData(form));
            fetch("{{ route('settings.integrations.update') }}", {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Sucesso!', text: data.message, customClass: { confirmButton: 'btn btn-success' } });
                }
            });
        };
    };

    handleUpdate('formAsaas');
    handleUpdate('formWhatsApp');
});
</script>
@endsection
