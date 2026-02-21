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
    <div class="text-center mb-10 mt-5">
        <div class="d-flex justify-content-center flex-column align-items-center mb-4">
            <div style="width: 150px; height: 150px; display: flex; align-items: center; justify-content: center;">
                @include('_partials.macros', ['width' => '150', 'height' => '150'])
            </div>
            <h1 class="fw-bolder text-primary mt-3" style="font-size: 3.5rem !important; letter-spacing: -1px;">{{ config('variables.templateName') }}</h1>
        </div>
        <h3 class="fw-bold mt-4">{{ __('Accountant Portal') }}</h3>
        <p class="text-muted fs-5">{{ __('Exclusive access for data verification and export for') }} <strong>{{ $company->name }}</strong></p>
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
            <span class="text-muted fw-light">{{ __('Fiscal') }} /</span> {{ __('Financial BPO') }}
        </h4>
        <div class="d-flex gap-2">
            @if(!$isPublic)
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ofxImportModal">
                <i class="ti tabler-file-import me-1"></i> {{ __('Import OFX') }}
            </button>
            @endif
            
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#fiscalConfigModal">
                <i class="ti tabler-settings me-1"></i> {{ __('Company data') }}
            </button>

            @if($isPublic)
            <a href="{{ route('login') }}" class="btn btn-label-secondary">
                <i class="ti tabler-logout me-1"></i> {{ __('Exit Portal') }}
            </a>
            @endif
            <div class="input-group w-auto">
                <span class="input-group-text">{{ __('From') }}</span>
                <input type="date" class="form-control" id="startDate" value="{{ $startDate }}" onchange="filterData()">
                <span class="input-group-text">{{ __('To') }}</span>
                <input type="date" class="form-control" id="endDate" value="{{ $endDate }}" onchange="filterData()">
            </div>
        </div>
    </div>

    <!-- KPI Cards BPO -->
    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-success text-white p-3"><h5>{{ __('Revenue') }} R$ {{ number_format($totals['revenue'], 2, ',', '.') }}</h5></div></div>
        <div class="col-md-3"><div class="card bg-danger text-white p-3"><h5>{{ __('Expenses') }} R$ {{ number_format($totals['expenses'], 2, ',', '.') }}</h5></div></div>
        <div class="col-md-3"><div class="card bg-primary text-white p-3"><h5>{{ __('Balance') }} R$ {{ number_format($totals['net_profit'], 2, ',', '.') }}</h5></div></div>
        <div class="col-md-3"><div class="card bg-info text-white p-3"><h5>{{ __('Audit') }} {{ $totals['audited_count'] }}/{{ max(1, $expenses->count()) }}</h5></div></div>
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
                    <h5 class="fw-bold mb-4">{{ __('Income Statement') }} ({{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }})</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr><th>{{ __('Description') }}</th><th class="text-end">{{ __('Value') }} (R$)</th></tr>
                            </thead>
                            <tbody>
                                <tr class="table-success">
                                    <td><strong>(+) {{ __('GROSS REVENUE (Sales and Services)') }}</strong></td>
                                    <td class="text-end text-success"><strong>{{ number_format($totals['revenue'], 2, ',', '.') }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="ps-4">{{ __('Revenue from Service Orders') }}</td>
                                    <td class="text-end">{{ number_format($revenue->sum(fn($os) => $os->total), 2, ',', '.') }}</td>
                                </tr>
                                <tr class="table-danger">
                                    <td><strong>(-) {{ __('OPERATING COSTS AND EXPENSES') }}</strong></td>
                                    <td class="text-end text-danger"><strong>{{ number_format($totals['expenses'], 2, ',', '.') }}</strong></td>
                                </tr>
                                @foreach($dreData as $category => $amount)
                                <tr>
                                    <td class="ps-4">{{ $category ?: __('Other Expenses') }}</td>
                                    <td class="text-end">{{ number_format($amount, 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                                <tr class="table-primary">
                                    <td><h5 class="mb-0">(=) {{ __('NET RESULT FOR THE PERIOD') }}</h5></td>
                                    <td class="text-end"><h5 class="mb-0 {{ $totals['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">R$ {{ number_format($totals['net_profit'], 2, ',', '.') }}</h5></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- ABA RECEITAS -->
            <div class="tab-pane fade" id="tab-rev" role="tabpanel">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('Invoices for the Period') }}</h6>
                    <div class="d-flex gap-2">
                        <a href="{{ route('accounting.export-xml', ['start_date' => $startDate, 'end_date' => $endDate, 'token' => request('token')]) }}" class="btn btn-sm btn-outline-primary">
                            <i class="ti tabler-file-code me-1"></i> {{ __('Export XMLs') }}
                        </a>
                        <a href="{{ route('accounting.export-pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'token' => request('token')]) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ti tabler-file-type-pdf me-1"></i> {{ __('Export PDFs') }}
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
        <form action="{{ $isPublic ? route('accounting.public.update-regime', $company->accountant_token) : route('settings.company-data.update') }}" method="POST" enctype="multipart/form-data" class="modal-content" id="formFiscalConfig">
            @csrf
            <div class="modal-header border-bottom">
                <h5 class="modal-title">{{ __('Company Fiscal Settings') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($isPublic)
                <div class="alert alert-info small mb-4">
                    <i class="ti tabler-info-circle me-1"></i> {{ __('As an accountant, you can only update the tax regime.') }}
                </div>
                @endif
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Corporate Name') }}</label>
                        <input type="text" name="company_name" class="form-control" value="{{ $company->name }}" {{ $isPublic ? 'readonly' : 'required' }}>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('CNPJ') }}</label>
                        <input type="text" name="cnpj" class="form-control" value="{{ $company->document_number ?? '' }}" {{ $isPublic ? 'readonly' : '' }}>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-primary">{{ __('Tax Regime') }}</label>
                        <select name="tax_regime" class="form-select border-primary" required>
                            <option value="">{{ __('Select') }}...</option>
                            <option value="Simples Nacional" {{ $company->tax_regime == 'Simples Nacional' ? 'selected' : '' }}>Simples Nacional</option>
                            <option value="Lucro Presumido" {{ $company->tax_regime == 'Lucro Presumido' ? 'selected' : '' }}>Lucro Presumido</option>
                            <option value="Lucro Real" {{ $company->tax_regime == 'Lucro Real' ? 'selected' : '' }}>Lucro Real</option>
                            <option value="MEI" {{ $company->tax_regime == 'MEI' ? 'selected' : '' }}>MEI</option>
                        </select>
                    </div>
                    @if(!$isPublic)
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Trade Name') }}</label>
                        <input type="text" name="trade_name" class="form-control" value="{{ $company->trade_name ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Contact Email') }}</label>
                        <input type="email" name="email" class="form-control" value="{{ $company->email }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('ZIP Code') }}</label>
                        <input type="text" name="zip_code" class="form-control" value="{{ $company->zip_code ?? '' }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">{{ __('City / State') }}</label>
                        <div class="input-group">
                            <input type="text" name="city" class="form-control" value="{{ $company->city ?? '' }}">
                            <input type="text" name="state" class="form-control" style="max-width: 80px;" value="{{ $company->state ?? '' }}" maxlength="2">
                        </div>
                    </div>
                    @endif
                    <hr class="my-2">
                    @if(!$isPublic)
                    <div class="col-12" id="accountantLinkContainer">
                        <label class="form-label text-primary fw-bold">{{ __('Direct Access Link for Accountant') }}</label>
                        @if($company->accountant_token)
                        <div class="input-group">
                            <input type="text" class="form-control" id="accountantLink" value="{{ route('accounting.public', $company->accountant_token) }}" readonly>
                            <button class="btn btn-outline-primary" type="button" onclick="copyAccountantLink()"><i class="ti tabler-copy"></i></button>
                        </div>
                        <small class="text-muted">{{ __('Send this link to your accountant. They will only have access to this fiscal screen.') }}</small>
                        @else
                        <div class="alert alert-warning p-2 small mb-0">{{ __('Link not generated yet.') }}</div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer border-top d-flex justify-content-between">
                <button type="button" class="btn btn-label-primary {{ $company->accountant_token ? 'd-none' : '' }}" id="btnGenerateToken" onclick="generateAccountantToken()">{{ __('Generate Access Link') }}</button>
                <div>
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function filterData() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        const isPublic = @json($isPublic);
        const token = "{{ $company->accountant_token ?? '' }}";
        
        let url = "";
        
        if (isPublic && token) {
            // Se for acesso público, mantém na rota do portal com o token
            url = `{{ url('portal-contador') }}/${token}?start_date=${start}&end_date=${end}`;
        } else {
            // Se for acesso logado, usa a rota padrão
            url = `{{ route('accounting.index') }}?start_date=${start}&end_date=${end}`;
        }
        
        window.location.href = url;
    }

    function generateAccountantToken() {
        const btn = document.getElementById('btnGenerateToken');
        btn.disabled = true;
        btn.innerHTML = '{{ __("Generating...") }}';

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
                Swal.fire({icon: 'success', title: '{{ __("Link Generated!") }}', timer: 1500, showConfirmButton: false})
                .then(() => location.reload());
            }
        });
    }

    function copyAccountantLink() {
        const copyText = document.getElementById("accountantLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        Swal.fire({icon: 'success', title: '{{ __("Link Copied!") }}', showConfirmButton: false, timer: 1500});
    }

    document.getElementById('formFiscalConfig').onsubmit = function(e) {
        e.preventDefault();
        const form = this;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ __("Saving...") }}';

        const formData = new FormData(form);
        
        fetch(form.getAttribute('action'), {
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
                    title: '{{ __("Success!") }}',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: '{{ __("Save Error") }}',
                    text: data.message || '{{ __("Check the information provided.") }}'
                });
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            Swal.fire({
                icon: 'error',
                title: '{{ __("Connection Error") }}',
                text: '{{ __("Could not communicate with the server.") }}'
            });
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    };
</script>
@endsection
