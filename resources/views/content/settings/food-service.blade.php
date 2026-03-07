@extends('layouts/layoutMaster')

@section('title', 'Configurações Food Service')

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Configurações /</span> Food Service
</h4>

<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header"><i class="ti tabler-settings me-2"></i>Parâmetros do Estabelecimento</h5>
      <div class="card-body">
        <form action="{{ route('settings.food-service.update') }}" method="POST">
          @csrf
          @method('PUT')

          <div class="row">
            <!-- Configurações de Impressão -->
            <div class="col-md-6 mb-4">
              <h6><i class="ti tabler-printer me-2 text-primary"></i>Impressão de Cupom (Balcão)</h6>
              <div class="mb-3">
                <label class="form-label">Nome da Impressora</label>
                <input type="text" name="printer_name" class="form-control" value="{{ $settings['printer_name'] ?? '' }}" placeholder="Ex: Impressora_Termica">
                <small class="text-muted">O nome deve ser exatamente como instalado no Windows/Linux.</small>
              </div>
              <div class="mb-3">
                <label class="form-label">Largura do Papel</label>
                <select name="paper_width" class="form-select">
                  <option value="80" {{ ($settings['paper_width'] ?? '') == '80' ? 'selected' : '' }}>80mm (Padrão)</option>
                  <option value="58" {{ ($settings['paper_width'] ?? '') == '58' ? 'selected' : '' }}>58mm (Estreito)</option>
                </select>
              </div>
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="auto_print" id="auto_print" value="1" {{ ($settings['auto_print'] ?? '') == '1' ? 'checked' : '' }}>
                <label class="form-check-input-label" for="auto_print">Imprimir cupom automaticamente ao finalizar venda</label>
              </div>
            </div>

            <!-- Configurações de Cozinha -->
            <div class="col-md-6 mb-4">
              <h6><i class="ti tabler-tools-kitchen-2 me-2 text-warning"></i>Impressão de Cozinha / Produção</h6>
              <div class="mb-3">
                <label class="form-label">Nome da Impressora da Cozinha</label>
                <input type="text" name="kitchen_printer_name" class="form-control" value="{{ $settings['kitchen_printer_name'] ?? '' }}" placeholder="Ex: Impressora_Cozinha">
              </div>
              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="print_kitchen_order" id="print_kitchen_order" value="1" {{ ($settings['print_kitchen_order'] ?? '') == '1' ? 'checked' : '' }}>
                <label class="form-check-input-label" for="print_kitchen_order">Enviar pedido para cozinha automaticamente</label>
              </div>
            </div>
          </div>

          <div class="pt-4 border-top">
            <button type="submit" class="btn btn-primary me-2">Salvar Configurações</button>
            <a href="{{ route('dashboard') }}" class="btn btn-label-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>

    <!-- Atalho para Formas de Pagamento -->
    <div class="card bg-label-primary border-0 shadow-none mb-4">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar avatar-md bg-primary rounded me-3">
            <i class="ti tabler-wallet text-white fs-3"></i>
          </div>
          <div class="me-auto">
            <h5 class="mb-0 text-primary">Formas de Pagamento</h5>
            <small>Configure quais opções aparecem no seu Balcão de Vendas (Dinheiro, PIX, Cartão...).</small>
          </div>
          <a href="{{ route('finance.payment-methods') }}" class="btn btn-primary">Configurar Agora</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
