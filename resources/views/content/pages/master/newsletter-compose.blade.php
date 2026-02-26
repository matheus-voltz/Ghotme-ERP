@extends('layouts/layoutMaster')

@section('title', 'Compor Newsletter Global')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/quill/typography.scss',
  'resources/assets/vendor/libs/quill/katex.scss',
  'resources/assets/vendor/libs/quill/editor.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
<style>
  .card-body { overflow-x: hidden; }
  #editor-container { max-width: 100%; width: 100%; border-radius: 0 0 0.5rem 0.5rem; height: 400px; }
  .ql-editor { word-break: break-word; overflow-wrap: break-word; white-space: normal; }
</style>
@endsection

@section('vendor-script')
@vite([
  'resources/assets/vendor/libs/quill/katex.js',
  'resources/assets/vendor/libs/quill/quill.js',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.js'
])
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quill = new Quill('#editor-container', {
        modules: { toolbar: [[{header:[1,2,false]}],['bold','italic','underline'],['image','code-block'],[{list:'ordered'},{list:'bullet'}]] },
        placeholder: 'Escreva a mensagem global...',
        theme: 'snow'
    });

    const form = document.getElementById('newsletter-form');
    form.addEventListener('submit', () => document.getElementById('content-input').value = quill.root.innerHTML);

    const aiModal = new bootstrap.Modal(document.getElementById('aiModal'));
    document.getElementById('btn-generate-ai').addEventListener('click', () => aiModal.show());

    document.getElementById('btn-confirm-ai').addEventListener('click', function() {
        const prompt = document.getElementById('ai-prompt').value;
        const btn = this;
        if (!prompt) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Gerando...';

        fetch('{{ route("newsletter.admin.generate-ai") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ prompt: prompt })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                quill.root.innerHTML = data.content;
                document.getElementById('subject').value = "Ghotme News: " + prompt;
                aiModal.hide();
            }
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Gerar Rascunho';
        });
    });
});
</script>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Nova Campanha Master (Global)</h5>
        <button type="button" class="btn btn-label-info" id="btn-generate-ai"><i class="ti tabler-robot me-1"></i> Gerar com IA</button>
      </div>
      <div class="card-body pt-6">
        <form action="{{ route('master.newsletter.send') }}" method="POST" id="newsletter-form">
          @csrf
          <div class="row mb-6">
            <div class="col-md-8">
              <label class="form-label">Assunto do E-mail</label>
              <input type="text" name="subject" id="subject" class="form-control" placeholder="Ex: Grandes novidades no Ghotme!" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Público Alvo</label>
              <select name="target" class="form-select" required>
                <option value="subscribers">Apenas Assinantes Landing Page</option>
                <option value="all_clients">Todos os Clientes de Todas as Empresas</option>
                <option value="both">Todos (Assinantes + Clientes)</option>
              </select>
            </div>
          </div>
          
          <div class="mb-6">
            <label class="form-label">Conteúdo</label>
            <div id="editor-container"></div>
            <input type="hidden" name="content" id="content-input">
          </div>

          <div class="d-flex justify-content-end gap-3">
            <a href="{{ route('master.dashboard') }}" class="btn btn-label-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-lg"><i class="ti tabler-send me-1"></i> DISPARAR CAMPANHA GLOBAL</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal IA -->
<div class="modal fade" id="aiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">IA Master Writer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <textarea id="ai-prompt" class="form-control" rows="3" placeholder="Sobre o que você quer falar com seus usuários?"></textarea>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-primary w-100" id="btn-confirm-ai">Gerar Rascunho</button></div>
    </div>
  </div>
</div>
@endsection
