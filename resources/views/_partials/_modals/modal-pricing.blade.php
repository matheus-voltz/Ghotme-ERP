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
          <h4 class="text-center mb-2">Planos </h4>
          <p class="text-center mb-0">Todos os planos incluem mais de 40 ferramentas e recursos avançados para impulsionar seu produto. Escolha o melhor plano para atender às suas necessidades.</p>
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
              <span class="badge badge-sm bg-label-primary rounded-1 mb-2 ">Ganhe 2 meses grátis</span>
            </div>
          </div>

          <div class="row gy-6">
            <!-- Basic -->
            <div class="col-xl mb-md-0">
              <div class="card border rounded shadow-none">
                <div class="card-body pt-12 p-5">
                  <div class="mt-3 mb-5 text-center">
                    <img src="{{ asset('assets/img/illustrations/page-pricing-basic.png') }}" alt="Basic Image" height="120" />
                  </div>
                  <h4 class="card-title text-center text-capitalize mb-1">Básico</h4>
                  <p class="text-center mb-5">Um começo simples para todos</p>
                  <div class="text-center h-px-50">
                    <div class="d-flex justify-content-center">
                      <sup class="h6 text-body pricing-currency mt-2 mb-0 me-1">R$</sup>
                      <h1 class="mb-0 text-primary">0</h1>
                      <sub class="h6 text-body pricing-duration mt-auto mb-1">/mês</sub>
                    </div>
                  </div>

                  <ul class="list-group ps-6 my-5 pt-9">
                    <li class="mb-4">30 dias plano Padrão</li>
                  </ul>

                  <button type="button" class="btn btn-label-success d-grid w-100" data-bs-dismiss="modal">Seu Plano Atual</button>
                </div>
              </div>
            </div>

            <!-- Pro -->
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
                  <p class="text-center mb-5">Para pequenas e médias empresas</p>
                  <div class="text-center h-px-50">
                    <div class="d-flex justify-content-center">
                      <sup class="h6 text-body pricing-currency mt-2 mb-0 me-1">R$</sup>
                      <h1 class="price-toggle price-yearly text-primary mb-0">149</h1>
                      <h1 class="price-toggle price-monthly text-primary mb-0 d-none">149</h1>
                      <sub class="h6 text-body pricing-duration mt-auto mb-1">/mês</sub>
                    </div>
                    <small class="price-yearly price-yearly-toggle text-body-secondary">R$ 1.490,00/ ano</small>
                  </div>

                    <ul class="list-group ps-6 my-5 pt-9">
                    <li class="mb-2"><strong>Indicado para:</strong></li>
                    <li class="mb-2">MEI</li>
                    <li class="mb-2">Pequenos negócios</li>
                    <br>
                    <li class="mb-4">Orçamento com integração via whatsapp</li>
                    <li class="mb-4">1 empresa (CNPJ)</li>
                    <li class="mb-4">Até 3 usuários</li>
                    <li class="mb-4">Funcionalidades essenciais do ERP</li>
                    <li class="mb-4">Atualizações inclusas</li>
                    <li class="mb-0">Suporte básico (horário comercial)</li>
                    </ul>

                    <button type="button" class="btn btn-primary d-grid w-100" data-bs-dismiss="modal">Upgrade</button>
                  </div>
                  </div>
                </div>


            <!-- Enterprise -->
            <div class="col-xl">
              <div class="card border rounded shadow-none">
                <div class="card-body pt-12 p-5">
                  <div class="mt-3 mb-5 text-center">
                    <img src="{{ asset('assets/img/illustrations/page-pricing-enterprise.png') }}" alt="Enterprise Image" height="120" />
                  </div>
                  <h4 class="card-title text-center text-capitalize mb-1">Enterprise</h4>
                  <p class="text-center mb-5">Solução para grandes organizações</p>

                  <div class="text-center h-px-50">
                    <div class="d-flex justify-content-center">
                      <sup class="h6 text-body pricing-currency mt-2 mb-0 me-1">R$</sup>
                      <h1 class="price-toggle price-yearly text-primary mb-0">279</h1>
                      <h1 class="price-toggle price-monthly text-primary mb-0 d-none">279</h1>
                      <sub class="h6 text-body pricing-duration mt-auto mb-1">/mês</sub>
                    </div>
                    <small class="price-yearly price-yearly-toggle text-body-secondary">R$ 2.790,00 / ano</small>
                  </div>

                  <ul class="list-group ps-6 my-5 pt-9">
                    <li class="mb-2"><strong>Indicado para:</strong></li>
                    <li class="mb-2">PJ</li>
                    <li class="mb-2">Empresas estruturadas</li>
                    <li class="mb-4">Quem quer crescer sem trocar de sistema</li>
                    <br>
                    <li class="mb-4">1 empresa (CNPJ)</li>
                    <li class="mb-4">Até 10 usuários</li>
                    <li class="mb-4">Todos os módulos do sistema</li>
                    <li class="mb-4">Prioridade no suporte</li>
                    <li class="mb-0">Recursos avançados (relatórios, integrações, automações)</li>
                    </ul>

                  <button type="button" class="btn btn-label-primary d-grid w-100" data-bs-dismiss="modal">Upgrade</button>
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