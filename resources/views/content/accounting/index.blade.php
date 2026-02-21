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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">{{ __('Fiscal') }} /</span> {{ __('BPO Financeiro') }}
        </h4>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ofxImportModal">
                <i class="ti tabler-file-import me-1"></i> {{ __('Import OFX') }}
            </button>
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#fiscalConfigModal">
                <i class="ti tabler-settings me-1"></i> {{ __('Company data') }}
            </button>
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
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-rev">{{ __('Revenue & Invoices') }}</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-exp">{{ __('Expenses & Audit') }}</button></li>
        </ul>
        <div class="tab-content p-0">
            <!-- ABA RECEITAS -->
            <div class="tab-pane fade show active" id="tab-rev" role="tabpanel">
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
                </div>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
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
