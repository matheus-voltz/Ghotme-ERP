@extends('layouts/layoutMaster')

@section('title', 'Importar Dados')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Configurações /</span> Importar Dados
</h4>

<div class="row">
  <!-- Importar Estoque -->
  <div class="col-md-6">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Importar Estoque (Peças/Itens)</h5>
        <a href="{{ route('settings.import.template', 'inventory') }}" class="btn btn-sm btn-outline-secondary">
          <i class="ti tabler-download me-1"></i> Baixar Modelo CSV
        </a>
      </div>
      <div class="card-body">
        <p class="text-muted small">Use este módulo para subir seu estoque de uma vez. O arquivo deve ser um <strong>CSV</strong> seguindo o modelo.</p>
        
        <form action="{{ route('settings.import.inventory') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label">Arquivo CSV</label>
            <input type="file" name="file" class="form-control" accept=".csv" required />
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="ti tabler-upload me-1"></i> Iniciar Importação de Estoque
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Importar Clientes -->
  <div class="col-md-6">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Importar Clientes (CRM)</h5>
        <a href="{{ route('settings.import.template', 'clients') }}" class="btn btn-sm btn-outline-secondary">
          <i class="ti tabler-download me-1"></i> Baixar Modelo CSV
        </a>
      </div>
      <div class="card-body">
        <p class="text-muted small">Migre sua base de clientes. O sistema identifica automaticamente se é Pessoa Física ou Jurídica pelo documento.</p>
        
        <form action="{{ route('settings.import.clients') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label">Arquivo CSV</label>
            <input type="file" name="file" class="form-control" accept=".csv" required />
          </div>
          <button type="submit" class="btn btn-info w-100">
            <i class="ti tabler-upload me-1"></i> Iniciar Importação de Clientes
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Importar Serviços -->
  <div class="col-md-6">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Importar Serviços</h5>
        <a href="{{ route('settings.import.template', 'services') }}" class="btn btn-sm btn-outline-secondary">
          <i class="ti tabler-download me-1"></i> Baixar Modelo CSV
        </a>
      </div>
      <div class="card-body">
        <p class="text-muted small">Importe sua tabela de preços de mão de obra. Inclua nome, descrição, preço e tempo estimado.</p>
        
        <form action="{{ route('settings.import.services') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label">Arquivo CSV</label>
            <input type="file" name="file" class="form-control" accept=".csv" required />
          </div>
          <button type="submit" class="btn btn-primary w-100">
            <i class="ti tabler-upload me-1"></i> Iniciar Importação de Serviços
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Importar Veículos -->
  <div class="col-md-6">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Importar {{ niche('entities') }}</h5>
        <a href="{{ route('settings.import.template', 'vehicles') }}" class="btn btn-sm btn-outline-secondary">
          <i class="ti tabler-download me-1"></i> Baixar Modelo CSV
        </a>
      </div>
      <div class="card-body">
        <p class="text-muted small">Vincule os ativos aos clientes. <strong>Atenção:</strong> O documento do proprietário deve ser igual ao cadastrado no sistema.</p>
        
        <form action="{{ route('settings.import.vehicles') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-3">
            <label class="form-label">Arquivo CSV</label>
            <input type="file" name="file" class="form-control" accept=".csv" required />
          </div>
          <button type="submit" class="btn btn-warning w-100">
            <i class="ti tabler-upload me-1"></i> Iniciar Importação de {{ niche('entities') }}
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Instruções -->
  <div class="col-12">
    <div class="card border-primary">
      <div class="card-body">
        <h6 class="fw-bold"><i class="ti tabler-info-circle text-primary me-2"></i> Instruções Importantes:</h6>
        <ul class="mb-0 mt-2">
          <li>Certifique-se de que o arquivo está salvo no formato <strong>CSV (separado por vírgulas)</strong>.</li>
          <li>A primeira linha (cabeçalho) deve ser exatamente igual ao do arquivo modelo.</li>
          <li>Para preços, use o formato decimal com ponto (ex: <code>150.50</code>).</li>
          <li>O sistema não importa duplicados se o CPF/CNPJ já existir no banco de dados da sua empresa.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

@if(session('success'))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({ icon: 'success', title: 'Sucesso!', text: "{{ session('success') }}", customClass: { confirmButton: 'btn btn-primary' }});
  });
</script>
@endif

@if(session('error'))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({ icon: 'error', title: 'Erro na Importação', text: "{{ session('error') }}", customClass: { confirmButton: 'btn btn-primary' }});
  });
</script>
@endif

@endsection
