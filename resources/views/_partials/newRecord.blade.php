
<div class="offcanvas offcanvas-end" id="add-new-record">
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title" id="exampleModalLabel">Novo Registro</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body flex-grow-1">
    <form class="add-new-record pt-0 row g-2" id="form-add-new-record" onsubmit="return false">
      @foreach($fields as $field)
       
        @php
          $type = $field['field_type'] ?? 'text';
        @endphp
        
        <div class="col-sm-12 form-control-validation">
          <label class="form-label" for="{{ $field['name'] }}">{{ $field['label'] }}</label>
          <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="icon-base ti {{ $field['icon'] }}"></i></span>
            @if ($type === 'textarea')
              <textarea
                id="{{ $field['name'] }}"
                name="{{ $field['name'] }}"
                class="form-control {{ $field['class'] }}"
                placeholder="{{ $field['placeholder'] }}"
              ></textarea>

            @elseif ($type === 'select' and isset($field['options']))
              <select
                name="{{ $field['name'] }}"
                id="{{ $field['field_key'] }}" class="select2 form-select selectpicker" data-allow-clear="true">
                
                <option value="">{{ $field['placeholder'] }}</option>
                @foreach ($field->options as $option)
                  <option value="{{ $option }}">
                    {{ $option }}
                  </option>
                @endforeach
              </select>
            @else
              <input
                type="{{ $type }}"
                id="{{ $field['field_key'] }}"
                name="{{ $field['field_key'] }}"
                class="form-control {{ $field['class'] }}"
                placeholder="{{ $field['placeholder'] }}"
                aria-label="{{ $field['placeholder'] }}"
                value=""
              />
            @endif
          </div>
        </div>
      @endforeach
      <br>
      <div class="col-sm-12">
        <button type="submit" class="btn btn-primary data-submit me-sm-4 me-1">Salvar</button>
        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('cep').addEventListener('blur', function () {
  let cep = this.value.replace(/\D/g, '');

  if (cep.length !== 8) return;

  fetch(`https://brasilapi.com.br/api/cep/v1/${cep}`)
    .then(response => {
      if (!response.ok) throw new Error('CEP não encontrado');
      return response.json();
    })
    .then(data => {
      document.getElementById('rua').value    = data.street || '';
      document.getElementById('bairro').value = data.neighborhood || '';
      document.getElementById('cidade').value = data.city || '';
      document.getElementById('estado').value     = data.state || '';
    })
    .catch(() => {
      alert('CEP inválido ou não encontrado');
    });
});
</script>