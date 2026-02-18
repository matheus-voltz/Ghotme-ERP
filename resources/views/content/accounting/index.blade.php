@extends('layouts/contentNavbarLayout')

@section('title', 'Portal da Contabilidade e Fiscal')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Fiscal /</span> Portal da Contabilidade
        </h4>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#fiscalConfigModal">
                <i class="ti tabler-settings me-1"></i> Configurar Dados Fiscais
            </button>
            <select class="form-select w-auto" id="monthFilter" onchange="filterData()">
                @php
                    $meses = [
                        '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março', '04' => 'Abril',
                        '05' => 'Maio', '06' => 'Junho', '07' => 'Julho', '08' => 'Agosto',
                        '09' => 'Setembro', '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
                    ];
                @endphp
                @foreach($meses as $key => $nome)
                <option value="{{ $key }}" {{ $month == $key ? 'selected' : '' }}>
                    {{ $nome }}
                </option>
                @endforeach
            </select>
            <select class="form-select w-auto" id="yearFilter" onchange="filterData()">
                @for($y=date('Y'); $y>=2024; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    <!-- Cards de Resumo Fiscal -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Receita Bruta</h5>
                    <h3 class="mb-0 text-white">R$ {{ number_format($totals['revenue'], 2, ',', '.') }}</h3>
                    <p class="mb-0 opacity-75">Período Selecionado</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Notas Emitidas</h5>
                    <h3 class="mb-0 text-white">R$ {{ number_format($totals['invoiced'], 2, ',', '.') }}</h3>
                    <p class="mb-0 opacity-75">{{ $invoices->where('status', 'authorized')->count() }} autorizadas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Imposto Previsto (ISS)</h5>
                    <h3 class="mb-0 text-white">R$ {{ number_format($totals['projected_tax'], 2, ',', '.') }}</h3>
                    <p class="mb-0 opacity-75">Alíquota Atual: {{ $company->iss_rate }}%</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title text-white">Arquivos XML</h5>
                    <div class="d-flex gap-2 mt-2">
                        <a href="{{ route('accounting.export') }}" class="btn btn-white btn-sm text-info shadow-sm w-100">
                            <i class="ti tabler-download me-1"></i> Exportar (ZIP)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Área de Gráficos e Tabelas -->
    <div class="row">
        <!-- Gráfico de Arrecadação Fiscal -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Cobertura Fiscal</h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    @php
                        $invoicingRate = ($totals['revenue'] > 0) ? ($totals['invoiced'] / $totals['revenue']) * 100 : 0;
                    @endphp
                    <div class="progress-circle mb-3" style="width: 150px; height: 150px; border: 15px solid #f8f7fa; border-radius: 50%; display: flex; align-items: center; justify-content: center; position: relative;">
                         <span class="h2 fw-bold mb-0 text-primary">{{ number_format($invoicingRate, 1) }}%</span>
                    </div>
                    <p class="text-center text-muted mb-0">Percentual de faturamento com nota fiscal emitida.</p>
                </div>
            </div>
        </div>

        <!-- Tabela de Movimentação Mensal -->
        <div class="col-md-8 mb-4">
            <div class="card h-100">
                <div class="card-header border-bottom">
                    <h5 class="card-title mb-0">Faturamento Diário e Notas</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>OS #</th>
                                <th>Cliente</th>
                                <th>Valor</th>
                                <th>Status NF</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            @php $invoice = $invoices->where('ordem_servico_id', $order->id)->first(); @endphp
                            <tr>
                                <td>{{ $order->updated_at->format('d/m') }}</td>
                                <td><strong>#{{ $order->id }}</strong></td>
                                <td>{{ \Illuminate\Support\Str::limit($order->client->name, 20) }}</td>
                                <td>R$ {{ number_format($order->total, 2, ',', '.') }}</td>
                                <td>
                                    @if($invoice)
                                        <span class="badge bg-label-success">Emitida ({{ $invoice->number }})</span>
                                    @else
                                        <a href="{{ route('tax.invoice.create', ['os' => $order->id]) }}" class="btn btn-xs btn-outline-primary">Emitir Agora</a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-4">Nenhuma OS finalizada no mês.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Configurações Fiscais -->
<div class="modal fade" id="fiscalConfigModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Configurações Fiscais da Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('settings.company-data.update') }}" method="POST">
                @csrf
                <div class="modal-body pt-0">
                    <p class="text-muted small mb-4">Estes dados serão usados para calcular impostos e emitir notas fiscais oficiais.</p>
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">CNPJ</label>
                            <input type="text" name="document_number" class="form-control" value="{{ $company->document_number }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Insc. Estadual (IE)</label>
                            <input type="text" name="ie" class="form-control" value="{{ $company->ie }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Insc. Municipal (IM)</label>
                            <input type="text" name="im" class="form-control" value="{{ $company->im }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Regime Tributário</label>
                            <select name="tax_regime" class="form-select">
                                <option value="simples_nacional" {{ $company->tax_regime == 'simples_nacional' ? 'selected' : '' }}>Simples Nacional</option>
                                <option value="mei" {{ $company->tax_regime == 'mei' ? 'selected' : '' }}>MEI</option>
                                <option value="lucro_presumido" {{ $company->tax_regime == 'lucro_presumido' ? 'selected' : '' }}>Lucro Presumido</option>
                                <option value="lucro_real" {{ $company->tax_regime == 'lucro_real' ? 'selected' : '' }}>Lucro Real</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alíquota de ISS (%)</label>
                            <div class="input-group">
                                <input type="number" name="iss_rate" class="form-control" step="0.01" value="{{ $company->iss_rate }}" required>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function filterData() {
        const month = document.getElementById('monthFilter').value;
        const year = document.getElementById('yearFilter').value;
        window.location.href = `{{ route('accounting.index') }}?month=${month}&year=${year}`;
    }
</script>
@endsection
