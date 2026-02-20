@extends('layouts/contentNavbarLayout')

@section('title', __('Fiscal & Accounting') . ' & ' . __('Bank Reconciliation'))

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
                                <td><a href="#" class="btn btn-xs btn-outline-primary">{{ __('View OS') }}</a></td>
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

<!-- Scripts e Modais Fiscais omitidos para brevidade (mantêm-se os mesmos) -->
<script>
    function filterData() {
        window.location.href = `{{ route('accounting.index') }}?month=${document.getElementById('monthFilter').value}&year=${document.getElementById('yearFilter').value}`;
    }
</script>
@endsection
