@extends('layouts/layoutMaster')

@section('title', 'Base de Conhecimento')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <h4 class="mb-4">Como podemos ajudar você hoje?</h4>
        <div class="input-group input-group-merge">
          <span class="input-group-text" id="basic-addon-search31"><i class="ti tabler-search"></i></span>
          <input type="text" class="form-control" placeholder="Busque por tutoriais ou dúvidas..." aria-label="Search..." aria-describedby="basic-addon-search31" />
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <div class="card mb-4">
      <div class="card-body text-center">
        <i class="ti tabler-file-text icon-xl mb-3 text-primary"></i>
        <h5>Primeiros Passos</h5>
        <p>Aprenda a cadastrar seus primeiros clientes e veículos no sistema.</p>
        <a href="javascript:void(0)" class="btn btn-label-primary">Ver tutoriais</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card mb-4">
      <div class="card-body text-center">
        <i class="ti tabler-wallet icon-xl mb-3 text-success"></i>
        <h5>Financeiro</h5>
        <p>Como gerenciar contas a pagar, receber e conciliação bancária.</p>
        <a href="javascript:void(0)" class="btn btn-label-success">Ver tutoriais</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card mb-4">
      <div class="card-body text-center">
        <i class="ti tabler-settings icon-xl mb-3 text-warning"></i>
        <h5>Configurações</h5>
        <p>Personalize os modelos de impressão e integre com o WhatsApp.</p>
        <a href="javascript:void(0)" class="btn btn-label-warning">Ver tutoriais</a>
      </div>
    </div>
  </div>
</div>

<div class="card bg-transparent shadow-none">
  <div class="card-header border-bottom mb-4 p-0 pb-3">
    <h5 class="mb-0">Perguntas Frequentes (FAQ)</h5>
  </div>
  <div class="card-body p-0">
    <div class="accordion accordion-flush" id="accordionFAQ">

      <!-- Existing Questions -->
      <div class="accordion-item border-0 mb-3 rounded-4 overflow-hidden shadow-sm">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed bg-white shadow-none fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
            <i class="ti tabler-file-invoice me-2 text-primary"></i> Como converter um orçamento em Ordem de Serviço?
          </button>
        </h2>
        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
          <div class="accordion-body bg-white text-muted small pt-0">
            Basta ir em <strong>Orçamentos > Aguardando aprovação</strong> e clicar no ícone de check verde na linha do orçamento desejado. O sistema criará a OS automaticamente copiando todos os itens.
          </div>
        </div>
      </div>

      <div class="accordion-item border-0 mb-3 rounded-4 overflow-hidden shadow-sm">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed bg-white shadow-none fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
            <i class="ti tabler-package me-2 text-success"></i> Como funciona a baixa automática de estoque?
          </button>
        </h2>
        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
          <div class="accordion-body bg-white text-muted small pt-0">
            Ao finalizar uma Ordem de Serviço que contenha peças, o sistema automaticamente debita as quantidades do seu inventário.
          </div>
        </div>
      </div>

      <!-- New Portal Questions -->
      <div class="accordion-item border-0 mb-3 rounded-4 overflow-hidden shadow-sm">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed bg-white shadow-none fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
            <i class="ti tabler-browser me-2 text-info"></i> O que meu cliente pode fazer no Portal do Cliente?
          </button>
        </h2>
        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
          <div class="accordion-body bg-white text-muted small pt-0">
            No portal, seu cliente pode acompanhar o status do serviço em tempo real (check-in, manutenção, testes, pronto), visualizar e aprovar orçamentos pendentes, e consultar o histórico de serviços realizados.
          </div>
        </div>
      </div>

      <div class="accordion-item border-0 mb-3 rounded-4 overflow-hidden shadow-sm">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed bg-white shadow-none fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
            <i class="ti tabler-click me-2 text-warning"></i> Como o cliente aprova um orçamento online?
          </button>
        </h2>
        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
          <div class="accordion-body bg-white text-muted small pt-0">
            Ao acessar o portal (via link enviado por WhatsApp/Email), o cliente verá uma notificação de "Orçamentos Pendentes". Ao clicar, ele vê os detalhes e pode clicar em "Aprovar Orçamento", o que atualiza o status no seu sistema instantaneamente.
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection