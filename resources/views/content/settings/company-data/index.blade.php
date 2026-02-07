@extends('layouts/layoutMaster')

@section('title', 'Dados da Empresa')

@section('vendor-style')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-6">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Configurações da Oficina</h5>
      </div>
      <div class="card-body pt-6">
        <form id="formCompanyData" enctype="multipart/form-data">
          @csrf
          <div class="row">
            <div class="col-md-6 mb-4">
              <label class="form-label">Razão Social</label>
              <input type="text" name="company_name" class="form-control" value="{{ $settings->company_name }}" placeholder="Nome da Empresa LTDA" />
            </div>
            <div class="col-md-6 mb-4">
              <label class="form-label">Nome Fantasia</label>
              <input type="text" name="trade_name" class="form-control" value="{{ $settings->trade_name }}" placeholder="Minha Oficina" />
            </div>
            <div class="col-md-4 mb-4">
              <label class="form-label">CNPJ</label>
              <input type="text" name="cnpj" class="form-control" value="{{ $settings->cnpj }}" placeholder="00.000.000/0000-00" />
            </div>
            <div class="col-md-4 mb-4">
              <label class="form-label">E-mail</label>
              <input type="email" name="email" class="form-control" value="{{ $settings->email }}" placeholder="contato@oficina.com" />
            </div>
            <div class="col-md-2 mb-4">
              <label class="form-label">Telefone</label>
              <input type="text" name="phone" class="form-control" value="{{ $settings->phone }}" placeholder="(00) 0000-0000" />
            </div>
            <div class="col-md-2 mb-4">
              <label class="form-label">WhatsApp/Celular</label>
              <input type="text" name="mobile" class="form-control" value="{{ $settings->mobile }}" placeholder="(00) 00000-0000" />
            </div>
          </div>

          <hr class="my-4">
          <h6 class="mb-4">Endereço</h6>
          <div class="row">
            <div class="col-md-2 mb-4">
              <label class="form-label">CEP</label>
              <input type="text" name="zip_code" class="form-control cep-lookup" value="{{ $settings->zip_code }}" placeholder="00000-000" />
            </div>
            <div class="col-md-8 mb-4">
              <label class="form-label">Logradouro (Rua/Av)</label>
              <input type="text" name="address" class="form-control" value="{{ $settings->address }}" placeholder="Rua Exemplo" />
            </div>
            <div class="col-md-2 mb-4">
              <label class="form-label">Número</label>
              <input type="text" name="number" class="form-control" value="{{ $settings->number }}" placeholder="123" />
            </div>
            <div class="col-md-4 mb-4">
              <label class="form-label">Bairro</label>
              <input type="text" name="neighborhood" class="form-control" value="{{ $settings->neighborhood }}" placeholder="Centro" />
            </div>
            <div class="col-md-4 mb-4">
              <label class="form-label">Cidade</label>
              <input type="text" name="city" class="form-control" value="{{ $settings->city }}" placeholder="São Paulo" />
            </div>
            <div class="col-md-4 mb-4">
              <label class="form-label">Estado (UF)</label>
              <input type="text" name="state" class="form-control" value="{{ $settings->state }}" placeholder="SP" maxlength="2" />
            </div>
          </div>

          <hr class="my-4">
          <div class="row align-items-center">
            <div class="col-md-6 mb-4">
              <label class="form-label">Logotipo da Oficina</label>
              <input type="file" name="logo" class="form-control" accept="image/*" />
              <small class="text-muted">Formatos aceitos: JPG, PNG. Tamanho máx: 2MB.</small>
            </div>
            <div class="col-md-6 text-center">
              @if($settings->logo_path)
                <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Logo" class="img-fluid rounded border p-2" style="max-height: 100px;">
              @else
                <div class="p-4 border rounded bg-light text-muted">Sem Logotipo</div>
              @endif
            </div>
          </div>

          <div class="mt-6">
            <button type="submit" class="btn btn-primary me-3">Salvar Configurações</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@section('page-script')
@vite(['resources/js/cep-lookup.js'])
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCompanyData');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        
        fetch("{{ route('settings.company-data.update') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: data.message,
                    customClass: { confirmButton: 'btn btn-success' }
                }).then(() => location.reload());
            }
        });
    });
});
</script>
@endsection
