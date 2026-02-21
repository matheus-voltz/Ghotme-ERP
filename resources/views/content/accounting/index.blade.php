@extends('layouts/contentNavbarLayout')

@section('title', __('Fiscal & Accounting') . ' & ' . __('Bank Reconciliation'))

@section('vendor-style')
@vite(['resources/assets/vendor/libs/animate-css/animate.scss', 'resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
@endsection

@section('vendor-script')
@vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if($isPublic)
    <div class="text-center mb-10 mt-4">
        <div class="app-brand justify-content-center flex-column mb-4">
            <a href="javascript:void(0);" class="app-brand-link flex-column gap-3">
                <span class="app-brand-logo demo" style="width: 150px; height: 150px;">
                    @include('_partials.macros', ['width' => 150, 'height' => 150, 'withbg' => "fill: #7367f0"])
                </span>
                <span class="app-brand-text demo menu-text fw-bolder fs-1 ms-0" style="font-size: 3rem !important;">{{ config('variables.templateName') }}</span>
            </a>
        </div>
        <h3 class="fw-bold mt-4">Portal do Contador</h3>
        <p class="text-muted fs-5">Acesso exclusivo para conferência e exportação de dados da empresa <strong>{{ $company->name }}</strong></p>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
      {{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">{{ __('Fiscal') }} /</span> {{ __('BPO Financeiro') }}
        </h4>
        <div class="d-flex gap-2">
            @if(!$isPublic)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ofxImportModal">
                <i class="ti tabler-file-import me-1"></i> {{ __('Import OFX') }}
            </button>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#fiscalConfigModal">
                <i class="ti tabler-settings me-1"></i> {{ __('Company data') }}
            </button>
            @else
            <a href="{{ route('login') }}" class="btn btn-label-secondary">
                <i class="ti tabler-logout me-1"></i> {{ __('Sair do Portal') }}
            </a>
            @endif
            <select class="form-select w-auto" id="monthFilter" onchange="filterData()">
                @php
                    $locale = app()->getLocale();
                    $meses = [
                        '01' => __('January'), '02' => __('February'), '03' => __('March'), '04' => __('April'),
                        '05' => __('May'), '06' => __('June'), '07' => __('July'), '08' => __('August'),
                        '09' => __('September'), '10' => __('October'), '11' => __('November'), '12' => __('December')
                    ];
                @endphp
                @foreach($meses as $key => $nome)
                <option value="{{ $key }}" {{ $month == $key ? 'selected' : '' }}>{{ $nome }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- KPI Cards BPO -->
    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-success text-white p-3"><h5>{{ __('Revenue') }} R$ {{ number_format($totals['revenue'], 2, ',', '.') }}</h5></div></div>
        <div class="col-md-3"><div class="card bg-danger text-white p-3"><h5>{{ __('Expenses') }} R$ {{ number_format($totals['expenses'], 2, ',', '.') }}</h5></div></div>
        <div class="col-md-3"><div class="card bg-primary text-white p-3"><h5>{{ __('Balance') }} R$ {{ number_format($totals['net_profit'], 2, ',', '.') }}</h5></div></div>
        <div class="col-md-3"><div class="card bg-info text-white p-3"><h5>{{ __('Audit') }} {{ $totals['audited_count'] }}/{{ $expenses->count() }}</h5></div></div>
    </div>

    <div class="nav-align-top mb-4">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-dre">{{ __('DRE (Profit & Loss)') }}</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-rev">{{ __('Revenue & Invoices') }}</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-exp">{{ __('Expenses & Audit') }}</button></li>
        </ul>
        <div class="tab-content p-0">
            <!-- ABA DRE -->
            <div class="tab-pane fade show active" id="tab-dre" role="tabpanel">
                <div class="p-4">
                    <h5 class="fw-bold mb-4">Demonstrativo de Resultado ({{ $month }}/{{ $year }})</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr><th>Descrição</th><th class="text-end">Valor (R$)</th></tr>
                            </thead>
                            <tbody>
                                <tr class="table-success">
                                    <td><strong>(+) RECEITA BRUTA (Vendas e Serviços)</strong></td>
                                    <td class="text-end text-success"><strong>{{ number_format($totals['revenue'], 2, ',', '.') }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">Receitas de Ordens de Serviço</td>
                                    <td class="text-end">{{ number_format($revenue->sum(fn($os) => $os->total), 2, ',', '.') }}</td>
                                </tr>
                                <tr class="table-danger">
                                    <td><strong>(-) CUSTOS E DESPESAS OPERACIONAIS</strong></td>
                                    <td class="text-end text-danger"><strong>{{ number_format($totals['expenses'], 2, ',', '.') }}</strong></td>
                                </tr>
                                @foreach($dreData as $category => $amount)
                                <tr>
                                    <td class="ps-4">{{ $category ?: 'Outras Despesas' }}</td>
                                    <td class="text-end">{{ number_format($amount, 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                                <tr class="table-primary">
                                    <td><h5 class="mb-0">(=) RESULTADO LÍQUIDO DO PERÍODO</h5></td>
                                    <td class="text-end"><h5 class="mb-0 {{ $totals['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">R$ {{ number_format($totals['net_profit'], 2, ',', '.') }}</h5></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- ABA RECEITAS -->
            <div class="tab-pane fade show active" id="tab-rev" role="tabpanel">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Notas Fiscais do Período</h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('accounting.export-xml', ['month' => $month, 'year' => $year, 'token' => request('token')]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="ti tabler-file-code me-1"></i> Exportar XMLs
                        </a>
                        <a href="{{ route('accounting.export-pdf', ['month' => $month, 'year' => $year, 'token' => request('token')]) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ti tabler-file-type-pdf me-1"></i> Exportar PDFs
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('OS #') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Value') }}</th><th>{{ __('Action') }}</th></tr></thead>
                        <tbody>
                            @foreach($revenue as $order)
                            <tr>
                                <td>{{ $order->updated_at->format('d/m') }}</td>
                                <td>#{{ $order->id }}</td>
                                <td>{{ $order->client->name }}</td>
                                <td>R$ {{ number_format($order->total, 2, ',', '.') }}</td>
                                <td><a href="{{ route('ordens-servico.edit', $order->id) }}" class="btn btn-xs btn-outline-primary">{{ __('View OS') }}</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- ABA DESPESAS -->
            <div class="tab-pane fade" id="tab-exp" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>{{ __('Date') }}</th><th>{{ __('Description') }}</th><th>{{ __('Value') }}</th><th>{{ __('Audit') }}</th><th>{{ __('Action') }}</th></tr></thead>
                        <tbody>
                            @foreach($expenses as $expense)
                            <tr>
                                <td>{{ date('d/m', strtotime($expense->due_date)) }}</td>
                                <td>{{ $expense->description }}</td>
                                <td class="text-danger">R$ {{ number_format($expense->amount, 2, ',', '.') }}</td>
                                <td><span class="badge bg-label-{{ $expense->audit_status == 'audited' ? 'success' : 'warning' }}">{{ __($expense->audit_status) }}</span></td>
                                <td><button class="btn btn-xs btn-outline-secondary" onclick="signalError({{ $expense->id }})">{{ __('Audit') }}</button></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Importar OFX -->
<div class="modal fade" id="ofxImportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('accounting.import-ofx') }}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">{{ __('Bank Reconciliation') }} (OFX)</h5></div>
            <div class="modal-body">
                <p>{{ __('Select the .ofx file exported by your bank.') }}</p>
                <input type="file" name="ofx_file" class="form-control" accept=".ofx" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Process File') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Dados da Empresa (Fiscal) -->
<div class="modal fade" id="fiscalConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('settings.company-data.update') }}" method="POST" enctype="multipart/form-data" class="modal-content" id="formFiscalConfig">
            @csrf
            <div class="modal-header border-bottom">
                <h5 class="modal-title">Configurações Fiscais da Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Razão Social</label>
                        <input type="text" name="company_name" class="form-control" value="{{ $company->name }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nome Fantasia</label>
                        <input type="text" name="trade_name" class="form-control" value="{{ $company->trade_name ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CNPJ</label>
                        <input type="text" name="cnpj" class="form-control" value="{{ $company->document ?? '' }}" placeholder="00.000.000/0000-00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">E-mail de Contato</label>
                        <input type="email" name="email" class="form-control" value="{{ $company->email }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">CEP</label>
                        <input type="text" name="zip_code" class="form-control" value="{{ $company->zip_code ?? '' }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Cidade / UF</label>
                        <div class="input-group">
                            <input type="text" name="city" class="form-control" value="{{ $company->city ?? '' }}">
                            <input type="text" name="state" class="form-control" style="max-width: 80px;" value="{{ $company->state ?? '' }}" maxlength="2">
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="col-12" id="accountantLinkContainer">
                        <label class="form-label text-primary fw-bold">Link de Acesso Direto para o Contador</label>
                        @if($company->accountant_token)
                        <div class="input-group">
                            <input type="text" class="form-control" id="accountantLink" value="{{ route('accounting.public', $company->accountant_token) }}" readonly>
                            <button class="btn btn-outline-primary" type="button" onclick="copyAccountantLink()"><i class="ti tabler-copy"></i></button>
                        </div>
                        <small class="text-muted">Envie este link para seu contador. Ele terá acesso apenas a esta tela fiscal.</small>
                        @else
                        <div class="alert alert-warning p-2 small mb-0">Link ainda não gerado.</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top d-flex justify-content-between">
                <button type="button" class="btn btn-label-primary {{ $company->accountant_token ? 'd-none' : '' }}" id="btnGenerateToken" onclick="generateAccountantToken()">Gerar Link de Acesso</button>
                <div>
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function generateAccountantToken() {
        const btn = document.getElementById('btnGenerateToken');
        btn.disabled = true;
        btn.innerHTML = 'Gerando...';

        fetch("{{ route('accounting.generate-token') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({icon: 'success', title: 'Link Gerado!', timer: 1500, showConfirmButton: false})
                .then(() => location.reload()); // Recarrega para mostrar o link
            }
        });
    }
    function copyAccountantLink() {
        const copyText = document.getElementById("accountantLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        Swal.fire({icon: 'success', title: 'Link Copiado!', showConfirmButton: false, timer: 1500});
    }
    function filterData() {
        window.location.href = `{{ route('accounting.index') }}?month=${document.getElementById('monthFilter').value}&year=${document.getElementById('yearFilter').value}`;
    }

    document.getElementById('formFiscalConfig').onsubmit = function(e) {
        e.preventDefault();
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Salvando...';

        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao salvar',
                    text: data.message || 'Verifique os dados informados.'
                });
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Erro de Conexão',
                text: 'Não foi possível se comunicar com o servidor.'
            });
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    };
</script>
@endsection
