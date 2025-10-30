@extends('adminlte::page')

@section('title', 'Visão Geral de Faturamento')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="fw-bold text-primary mb-0">
            <i class="fas fa-file-invoice-dollar"></i>
            Visão Geral de Faturamento
        </h1>
        
        <a href="#" class="btn btn-primary shadow-sm">
            <i class="fas fa-list me-1"></i>
            Ver Faturas
        </a>
    </div>
@stop

@section('content')
<div class="container-fluid">

    <!-- ===== Card da Tabela de Faturamento ===== -->
    <div class="card card-filter mb-4 shadow-lg border-0">
        <div class="card-header bg-secondary text-white">
            <h3 class="card-title mb-0">
                <i class="fas fa-chart-bar me-2"></i>Resumo por Cliente e Período
            </h3>
        </div>

        <div class="card-body p-0"> {{-- p-0 para a tabela preencher o card --}}
            <div class="table-responsive">
                
                <table class="table table-hover table-striped align-middle mb-0">
                    <!-- Cabeçalho da Tabela -->
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="px-3">
                                Cliente
                            </th>
                            <th scope="col" class="px-3">
                                Período (Mês/Ano)
                            </th>
                            <th scope="col" class="text-right px-3">
                                Valor Total Transacionado
                            </th>
                            <th scope="col" class="text-right px-3">
                                IR Total
                            </th>
                        </tr>
                    </thead>
                    
                    <!-- Corpo da Tabela -->
                    <tbody>
                        @forelse ($agrupamentos as $item)
                            <tr>
                                <td class="px-3">
                                    {{ $item->cliente_nome }}
                                    <span class="d-block text-muted small">(ID: {{ $item->cliente_id }})</span>
                                </td>
                                <td class="px-3">
                                    {{ $item->mes_ano }}
                                </td>
                                <td class="text-right text-monospace px-3">
                                    R$ {{ number_format($item->valor_bruto_total, 2, ',', '.') }}
                                </td>
                                <td class="text-right text-monospace text-danger fw-bold px-3">
                                    R$ {{ number_format($item->ir_total, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <!-- Caso não haja dados -->
                            <tr>
                                <td colspan="4">
                                    <div class="alert alert-light text-center m-3">
                                        Nenhum dado de faturamento encontrado para o período.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div> <!-- Fim do Card Body -->
    </div> <!-- Fim do Card Principal -->

</div>
@stop

@push('css')
<style>
    .text-monospace {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }
</style>
@endpush

{{-- Adiciona JS customizado se necessário --}}
@push('js')
<script>
    // console.log('Página de Faturamento carregada!');
</script>
@endpush

