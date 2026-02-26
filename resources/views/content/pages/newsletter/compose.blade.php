@extends('layouts/layoutMaster')

@section('title', 'Compor Newsletter')

@section('vendor-style')
@vite([
  'resources/assets/vendor/libs/quill/typography.scss',
  'resources/assets/vendor/libs/quill/katex.scss',
  'resources/assets/vendor/libs/quill/editor.scss',
  'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'
])
<style>
  .card-body {
    overflow-x: hidden;
  }
  #editor-container {
    max-width: 100%;
    width: 100%;
    border-radius: 0 0 0.5rem 0.5rem;
  }
  .ql-editor {
    word-break: break-word;
    overflow-wrap: break-word;
    white-space: normal;
  }
  .ql-editor * {
    max-width: 100% !important;
    word-break: break-word !important;
  }
  .ql-container.ql-snow {
    border: 1px solid #dbdade;
  }
  .ql-toolbar.ql-snow {
    border: 1px solid #dbdade;
    border-bottom: none;
    border-radius: 0.5rem 0.5rem 0 0;
  }
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
    // Inicializa Quill
    const quill = new Quill('#editor-container', {
        modules: {
            toolbar: [
                [{ header: [1, 2, false] }],
                ['bold', 'italic', 'underline'],
                ['image', 'code-block'],
                [{ list: 'ordered' }, { list: 'bullet' }]
            ]
        },
        placeholder: 'Escreva sua mensagem aqui...',
        theme: 'snow'
    });

    const form = document.getElementById('newsletter-form');
    if (form) {
      form.addEventListener('submit', function() {
          document.getElementById('content-input').value = quill.root.innerHTML;
      });
    }

    // Lógica da IA
    const btnAi = document.getElementById('btn-generate-ai');
    const aiModalEl = document.getElementById('aiModal');
    let aiModal = null;
    
    if (aiModalEl) {
      aiModal = new bootstrap.Modal(aiModalEl);
      btnAi.addEventListener('click', () => aiModal.show());
    }

    const btnConfirmAi = document.getElementById('btn-confirm-ai');
    if (btnConfirmAi) {
      btnConfirmAi.addEventListener('click', function() {
          const prompt = document.getElementById('ai-prompt').value;
          const btn = this;
          
          if (!prompt) {
            Swal.fire({ icon: 'warning', title: 'Atenção', text: 'Por favor, descreva o tema.' });
            return;
          }

          btn.disabled = true;
          btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Gerando rascunho com IA...';

          fetch('{{ route("newsletter.admin.generate-ai") }}', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              body: JSON.stringify({ prompt: prompt })
          })
          .then(res => res.json())
          .then(data => {
              if (data.success) {
                  quill.root.innerHTML = data.content;
                  document.getElementById('subject').value = "Informativo Ghotme: " + prompt;
                  if (aiModal) aiModal.hide();
                  Swal.fire({ icon: 'success', title: 'Sucesso!', text: 'Rascunho gerado com sucesso.', timer: 2000, showConfirmButton: false });
              } else {
                  Swal.fire({ icon: 'error', title: 'Erro na IA', text: data.message });
              }
          })
          .catch(err => {
              console.error(err);
              Swal.fire({ icon: 'error', title: 'Erro de Conexão', text: 'Não foi possível contatar o assistente de IA.' });
          })
          .finally(() => {
              btn.disabled = false;
              btn.innerHTML = 'Gerar Rascunho';
          });
      });
    }
});
</script>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Compor Nova Newsletter</h5>
        <button type="button" class="btn btn-label-info" id="btn-generate-ai">
          <i class="ti tabler-robot me-1"></i> Gerar Conteúdo com IA
        </button>
      </div>
      <div class="card-body pt-6">
        <form action="{{ route('newsletter.admin.store') }}" method="POST" id="newsletter-form">
          @csrf
          <div class="mb-6">
            <label class="form-label">Assunto do E-mail</label>
            <input type="text" name="subject" id="subject" class="form-control" placeholder="Ex: 5 Dicas para sua Oficina lucrar mais neste mês" required>
          </div>
          
          <div class="mb-6">
            <label class="form-label">Conteúdo</label>
            <div id="editor-container" style="height: 400px;"></div>
            <input type="hidden" name="content" id="content-input">
          </div>

          <div class="d-flex justify-content-end gap-3">
            <a href="{{ route('newsletter.admin.index') }}" class="btn btn-label-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">
              <i class="ti tabler-send me-1"></i> Enviar para todos os assinantes
            </button>
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
      <div class="modal-header">
        <h5 class="modal-title">Assistente de Conteúdo IA</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Sobre o que você quer falar?</label>
          <textarea id="ai-prompt" class="form-control" rows="3" placeholder="Ex: Escreva um e-mail com 3 dicas de gestão financeira para pequenas oficinas mecânicas."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary w-100" id="btn-confirm-ai">Gerar Rascunho</button>
      </div>
    </div>
  </div>
</div>
@endsection
