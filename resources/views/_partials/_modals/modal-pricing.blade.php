@push('pricing-script')
@vite(['resources/assets/js/pages-pricing.js'])
@endpush

<!-- Pricing Modal -->
<div class="modal fade" id="pricingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-simple modal-pricing">
    <div class="modal-content">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <!-- Pricing Plans -->
        <div class="rounded-top">
          <h4 class="text-center mb-2">Planos</h4>
          <p class="text-center mb-0">Todas as ferramentas que sua empresa precisa para crescer. Escolha o plano ideal.</p>
          <div class="d-flex align-items-center justify-content-center flex-wrap gap-2 pt-12 pb-4">
            <label class="switch switch-sm ms-sm-12 ps-sm-12 me-0">
              <span class="switch-label fs-6 text-body">Mensal</span>
              <input type="checkbox" class="switch-input price-duration-toggler" checked />
              <span class="switch-toggle-slider">
                <span class="switch-on"></span>
                <span class="switch-off"></span>
              </span>
              <span class="switch-label fs-6 text-body">Anual</span>
            </label>
            <div class="mt-n5 ms-n10 ml-2 mb-12 d-none d-sm-flex align-items-center gap-1">
              <i class="icon-base ti tabler-corner-left-down icon-lg text-body-secondary scaleX-n1-rtl"></i>
              <span class="badge badge-sm bg-label-primary rounded-1 mb-2">Ganhe 2 meses grátis</span>
            </div>
          </div>

          <div class="row gy-6">
            <!-- Básico / Trial -->
            <div class="col-xl mb-md-0">
              <div class="card border rounded shadow-none">
                <div class="card-body pt-12 p-5">
                  <div class="mt-3 mb-5 text-center">
                    <img src="{{ asset('assets/img/illustrations/page-pricing-basic.png') }}" alt="Basic Image" height="120" />
                  </div>
                  <h4 class="card-title text-center text-capitalize mb-1">Básico</h4>
                  <p class="text-center mb-5">Experimente gratuitamente</p>
                  <div class="text-center h-px-50">
                    <div class="d-flex justify-content-center">
                      <sup class="h6 text-body pricing-currency mt-2 mb-0 me-1">R$</sup>
                      <h1 class="mb-0 text-primary">0</h1>
                      <sub class="h6 text-body pricing-duration mt-auto mb-1">/mês</sub>
                    </div>
                  </div>

                  <ul class="list-unstyled my-5 pt-9">
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-success mt-1 flex-shrink-0"></i>
                      <span>30 dias de acesso ao plano Padrão</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-success mt-1 flex-shrink-0"></i>
                      <span>Acesso a todos os módulos essenciais</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-success mt-1 flex-shrink-0"></i>
                      <span>Sem necessidade de cartão de crédito</span>
                    </li>
                  </ul>

                  <button type="button" class="btn btn-label-success d-grid w-100" data-bs-dismiss="modal">Seu Plano Atual</button>
                </div>
              </div>
            </div>

            <!-- Padrão -->
            <div class="col-xl mb-md-0">
              <div class="card border-primary border shadow-none">
                <div class="card-body position-relative pt-4 p-5">
                  <div class="position-absolute end-0 me-5 top-0 mt-4">
                    <span class="badge bg-label-primary rounded-1">Popular</span>
                  </div>
                  <div class="my-5 pt-6 text-center">
                    <img src="{{ asset('assets/img/illustrations/page-pricing-standard.png') }}" alt="Standard Image" height="120" />
                  </div>
                  <h4 class="card-title text-center text-capitalize mb-1">Padrão</h4>
                  <p class="text-center mb-5">Para MEIs e pequenas empresas</p>
                  <div class="text-center h-px-50">
                    <div class="d-flex justify-content-center">
                      <sup class="h6 text-body pricing-currency mt-2 mb-0 me-1">R$</sup>
                      <h1 class="price-toggle price-yearly text-primary mb-0">149</h1>
                      <h1 class="price-toggle price-monthly text-primary mb-0 d-none">149</h1>
                      <sub class="h6 text-body pricing-duration mt-auto mb-1">/mês</sub>
                    </div>
                    <small class="price-yearly price-yearly-toggle text-body-secondary">R$ 1.490,00 / ano</small>
                  </div>

                  <ul class="list-unstyled my-5 pt-9">
                    <li class="mb-2"><strong class="text-body">Indicado para:</strong></li>
                    <li class="mb-3 text-muted ms-1 small">MEI e pequenos negócios</li>

                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-primary mt-1 flex-shrink-0"></i>
                      <span>1 empresa (CNPJ)</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-primary mt-1 flex-shrink-0"></i>
                      <span>Até 3 usuários</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-primary mt-1 flex-shrink-0"></i>
                      <span>Ordens de Serviço completas (timer, checklists, fotos)</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-primary mt-1 flex-shrink-0"></i>
                      <span>CRM de Clientes com histórico</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-primary mt-1 flex-shrink-0"></i>
                      <span>Financeiro (entradas, saídas e fluxo de caixa)</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-primary mt-1 flex-shrink-0"></i>
                      <span>Controle de Estoque com alertas</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-primary mt-1 flex-shrink-0"></i>
                      <span>Orçamentos com aprovação via WhatsApp</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-primary mt-1 flex-shrink-0"></i>
                      <span>Kanban de Ordens de Serviço</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-primary mt-1 flex-shrink-0"></i>
                      <span>Portal do Cliente (link público)</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-primary mt-1 flex-shrink-0"></i>
                      <span>Consultor IA – 10 análises por mês</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-primary mt-1 flex-shrink-0"></i>
                      <span>Atualizações sempre inclusas</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-primary mt-1 flex-shrink-0"></i>
                      <span>Suporte básico (horário comercial)</span>
                    </li>
                  </ul>

                  <button type="button" class="btn btn-primary d-grid w-100 btn-upgrade-plan" data-plan="padrao">Assinar Padrão</button>
                </div>
              </div>
            </div>

            <!-- Enterprise -->
            <div class="col-xl">
              <div class="card border rounded shadow-none" style="border-color: #ff9f43 !important;">
                <div class="card-body pt-12 p-5">
                  <div class="position-absolute end-0 me-5 top-0 mt-4">
                    <span class="badge bg-label-warning rounded-1"><i class="ti tabler-crown me-1"></i>Premium</span>
                  </div>
                  <div class="mt-3 mb-5 text-center">
                    <img src="{{ asset('assets/img/illustrations/page-pricing-enterprise.png') }}" alt="Enterprise Image" height="120" />
                  </div>
                  <h4 class="card-title text-center text-capitalize mb-1">Enterprise</h4>
                  <p class="text-center mb-5">Para empresas estruturadas e equipes maiores</p>

                  <div class="text-center h-px-50">
                    <div class="d-flex justify-content-center">
                      <sup class="h6 text-body pricing-currency mt-2 mb-0 me-1">R$</sup>
                      <h1 class="price-toggle price-yearly text-primary mb-0">279</h1>
                      <h1 class="price-toggle price-monthly text-primary mb-0 d-none">279</h1>
                      <sub class="h6 text-body pricing-duration mt-auto mb-1">/mês</sub>
                    </div>
                    <small class="price-yearly price-yearly-toggle text-body-secondary">R$ 2.790,00 / ano</small>
                  </div>

                  <ul class="list-unstyled my-5 pt-9">
                    <li class="mb-2"><strong class="text-body">Indicado para:</strong></li>
                    <li class="mb-3 text-muted ms-1 small">PJ e empresas que querem crescer sem trocar de sistema</li>

                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-warning mt-1 flex-shrink-0"></i>
                      <span><strong>Tudo do Padrão</strong>, mais:</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-warning mt-1 flex-shrink-0"></i>
                      <span>Até 10 usuários</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-warning mt-1 flex-shrink-0"></i>
                      <span>Consultor IA <strong>Ilimitado</strong></span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-warning mt-1 flex-shrink-0"></i>
                      <span>Robô de Reposição de Estoque</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-warning mt-1 flex-shrink-0"></i>
                      <span>BPO Financeiro (gestão terceirizada)</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-warning mt-1 flex-shrink-0"></i>
                      <span>Portal do Contador (acesso externo)</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-warning mt-1 flex-shrink-0"></i>
                      <span>Emissão Ilimitada de Notas Fiscais</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-warning mt-1 flex-shrink-0"></i>
                      <span>Conciliação Bancária (OFX)</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-warning mt-1 flex-shrink-0"></i>
                      <span>Integrações Externas (Mercado Livre, APIs)</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-warning mt-1 flex-shrink-0"></i>
                      <span>Acesso via API Token (para apps e integrações)</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-warning mt-1 flex-shrink-0"></i>
                      <span>Relatórios Avançados e Exportações</span>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                      <i class="ti tabler-circle-check text-warning mt-1 flex-shrink-0"></i>
                      <span>Suporte Prioritário</span>
                    </li>
                  </ul>

                  <button type="button" class="btn btn-warning d-grid w-100 btn-upgrade-plan text-white fw-bold" data-plan="enterprise">
                    <i class="ti tabler-crown me-1"></i> Assinar Enterprise
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!--/ Pricing Plans -->
      </div>
    </div>
  </div>
</div>
<!--/ Pricing Modal -->