@extends('layouts/layoutMaster')

@section('title', 'Base de Conhecimento')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <h4 class="mb-4">Como podemos ajudar você hoje?</h4>
        <div class="input-group input-group-merge">
          <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
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
        <i class="ti ti-file-text icon-xl mb-3 text-primary"></i>
        <h5>Primeiros Passos</h5>
        <p>Aprenda a cadastrar seus primeiros clientes e veículos no sistema.</p>
        <a href="javascript:void(0)" class="btn btn-label-primary">Ver tutoriais</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card mb-4">
      <div class="card-body text-center">
        <i class="ti ti-wallet icon-xl mb-3 text-success"></i>
        <h5>Financeiro</h5>
        <p>Como gerenciar contas a pagar, receber e conciliação bancária.</p>
        <a href="javascript:void(0)" class="btn btn-label-success">Ver tutoriais</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card mb-4">
      <div class="card-body text-center">
        <i class="ti ti-settings icon-xl mb-3 text-warning"></i>
        <h5>Configurações</h5>
        <p>Personalize os modelos de impressão e integre com o WhatsApp.</p>
        <a href="javascript:void(0)" class="btn btn-label-warning">Ver tutoriais</a>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header border-bottom">
    <h5 class="mb-0">Perguntas Frequentes (FAQ)</h5>
  </div>
  <div class="card-body pt-4">
    <div class="accordion" id="accordionFAQ">
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
            Como converter um orçamento em Ordem de Serviço?
          </button>
        </h2>
        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
          <div class="accordion-body">
            Basta ir em <strong>Orçamentos > Aguardando aprovação</strong> e clicar no ícone de check verde na linha do orçamento desejado. O sistema criará a OS automaticamente copiando todos os itens.
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
            Como funciona a baixa automática de estoque?
          </button>
        </h2>
        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#accordionFAQ">
          <div class="accordion-body">
            Ao finalizar uma Ordem de Serviço que contenha peças, o sistema automaticamente debita as quantidades do seu inventário.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
