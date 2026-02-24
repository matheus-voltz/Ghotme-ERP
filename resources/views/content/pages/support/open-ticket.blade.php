@extends('layouts/layoutMaster')

@section('title', 'Abrir Chamado')

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h5 class="card-title mb-0">Novo Chamado de Suporte</h5>
      </div>
      <div class="card-body pt-4">
        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
          <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('support.open-ticket.send') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label">Assunto</label>
            <input type="text" name="subject" class="form-control" placeholder="Ex: Problema na impressão de OS" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Prioridade</label>
            <select name="priority" class="form-select" required>
              <option value="low">Baixa (Dúvidas, sugestões)</option>
              <option value="medium">Média (Algo não está funcionando como deveria)</option>
              <option value="high">Alta (Sistema travado, erro crítico)</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Descreva seu problema ou sugestão</label>
            <textarea name="message" class="form-control" rows="6" placeholder="Forneça o máximo de detalhes possível..." required></textarea>
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Enviar Chamado</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card bg-primary text-white">
      <div class="card-body">
        <h5 class="card-title text-white mb-3">Atendimento Rápido</h5>
        <p class="card-text">Se o seu problema for urgente, prefira nosso contato via WhatsApp para uma resposta imediata.</p>
        <a href="{{ route('support.whatsapp') }}" target="_blank" class="btn btn-light w-100 mt-2">
          <i class="ti tabler-brand-whatsapp me-1"></i> Falar no WhatsApp
        </a>
      </div>
    </div>
    
    <div class="card mt-4">
      <div class="card-body">
        <h6>Horário de Funcionamento</h6>
        <p class="mb-1"><small>Segunda a Sexta:</small><br>08:00 às 18:00</p>
        <p class="mb-0"><small>Sábado:</small><br>08:00 às 12:00</p>
      </div>
    </div>
  </div>
</div>
@endsection
