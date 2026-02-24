@extends('layouts/layoutMaster')

@section('title', 'Campos Personalizados')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Configurações /</span> Campos Personalizados
</h4>

<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Adicionar Novo Campo</h5>
      <div class="card-body">
        <form action="{{ route('settings.custom-fields.store') }}" method="POST">
          @csrf
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" for="entity_type">Módulo</label>
              <select id="entity_type" name="entity_type" class="form-select" required>
                <option value="Clients">{{ niche_translate('Clientes') }}</option>
                <option value="Vehicles">{{ niche_translate('Veículos') }} / Ativos</option>
                <option value="OrdemServico">Ordens de Serviço</option>
                <option value="InventoryItem">{{ niche_translate('Peças') }} / Estoque</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="name">Nome do Campo</label>
              <input type="text" id="name" name="name" class="form-control" placeholder="Ex: Raça do Animal, Senha Wi-Fi" required />
            </div>
            <div class="col-md-4">
              <label class="form-label" for="type">Tipo de Campo</label>
              <select id="type" name="type" class="form-select" required>
                <option value="text">Texto Curto</option>
                <option value="textarea">Texto Longo</option>
                <option value="number">Número</option>
                <option value="date">Data</option>
                <option value="select">Seleção (Dropdown)</option>
              </select>
            </div>
            <div class="col-md-12 options-container d-none">
              <label class="form-label" for="options">Opções (Separadas por vírgula)</label>
              <input type="text" id="options" name="options" class="form-control" placeholder="Opção 1, Opção 2, Opção 3" />
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="required" name="required" value="1" />
                <label class="form-check-label" for="required"> Obrigatório </label>
              </div>
            </div>
            <div class="col-md-2">
              <label class="form-label" for="order">Ordem</label>
              <input type="number" id="order" name="order" class="form-control" value="0" />
            </div>
            <div class="col-md-8 d-flex align-items-end justify-content-end">
              <button type="submit" class="btn btn-primary">Criar Campo</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Lista de Campos -->
    <div class="card">
      <h5 class="card-header">Campos Configurados</h5>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Módulo</th>
              <th>Nome</th>
              <th>Tipo</th>
              <th>Obrigatório</th>
              <th>Ordem</th>
              <th>Status</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse($fields as $field)
            <tr>
              <td>
                <span class="badge bg-label-info">
                  @switch($field->entity_type)
                    @case('Clients') {{ niche_translate('Clientes') }} @break
                    @case('Vehicles') {{ niche_translate('Veículos') }} @break
                    @case('OrdemServico') Ordens de Serviço @break
                    @case('InventoryItem') {{ niche_translate('Peças') }} @break
                    @default {{ $field->entity_type }}
                  @endswitch
                </span>
              </td>
              <td><strong>{{ $field->name }}</strong></td>
              <td>{{ $field->type }}</td>
              <td>{{ $field->required ? 'Sim' : 'Não' }}</td>
              <td>{{ $field->order }}</td>
              <td><span class="badge bg-label-{{ $field->is_active ? 'success' : 'danger' }}">{{ $field->is_active ? 'Ativo' : 'Inativo' }}</span></td>
              <td>
                <form action="{{ route('settings.custom-fields.destroy', $field->id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-icon text-danger"><i class="ti tabler-trash"></i></button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center">Nenhum campo personalizado configurado.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('type').addEventListener('change', function() {
    const optionsContainer = document.querySelector('.options-container');
    if (this.value === 'select') {
      optionsContainer.classList.remove('d-none');
    } else {
      optionsContainer.classList.add('d-none');
    }
  });
</script>
@endsection
