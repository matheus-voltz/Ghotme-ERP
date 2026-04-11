@extends('layouts/contentNavbarLayout')

@section('title', 'SQL Runner')

@section('content')
<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Master /</span> SQL Runner
</h4>

<div class="row">
    <div class="col-12">
        <div class="card mb-4 border border-danger">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-danger"><i class="ti tabler-brand-mysql me-2"></i>Executar Query SQL Genérica</h5>
                <small class="text-muted float-end">Apenas is_master</small>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-4">
                    <h6 class="alert-heading mb-1"><i class="ti tabler-alert-triangle me-1"></i> Atenção!</h6>
                    <span>As queries aqui têm acesso direto e total ao banco de dados e são executadas na mesma hora. Cuidado com operações destrutivas como <strong>UPDATE</strong>, <strong>DELETE</strong> e <strong>DROP</strong>!</span>
                </div>

                <form action="{{ route('master.sql') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="sql_query">Sua Query</label>
                        <textarea id="sql_query" name="sql_query" class="form-control text-monospace bg-dark text-white p-3" rows="6" placeholder="SELECT * FROM users LIMIT 10;" style="font-family: monospace;">{{ old('sql_query', $query ?? '') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-danger"><i class="ti tabler-player-play me-1"></i> Executar Query</button>
                    <a href="{{ route('master.sql') }}" class="btn btn-outline-secondary ms-2">Limpar</a>
                </form>
            </div>
        </div>

        @if(isset($error) && $error)
        <div class="alert alert-danger alert-dismissible mb-4" role="alert">
            <h6 class="alert-heading mb-1">Erro de Execução SQL</h6>
            <p class="mb-0">{{ $error }}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(isset($successMsg) && $successMsg)
        <div class="alert alert-success alert-dismissible mb-4" role="alert">
            <p class="mb-0">{{ $successMsg }}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(isset($results) && is_array($results))
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Resultados da Query ({{ count($results) }} linhas retornadas)</h5>
            </div>
            
            <div class="card-body">
                @if(count($results) > 0)
                <div class="table-responsive text-nowrap" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light sticky-top">
                            <tr>
                                @foreach(array_keys((array)$results[0]) as $column)
                                    <th>{{ $column }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $row)
                                <tr>
                                    @foreach((array)$row as $column => $value)
                                        <td>
                                            @if(is_array($value) || is_object($value))
                                                <pre class="mb-0" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">{{ json_encode($value) }}</pre>
                                            @else
                                                <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $value }}">
                                                    {{ $value !== null ? $value : 'NULL' }}
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-info mb-0">A query rodou com sucesso mas retornou 0 resultados.</div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
