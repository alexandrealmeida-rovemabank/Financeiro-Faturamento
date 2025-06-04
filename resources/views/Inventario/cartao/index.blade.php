@extends('adminlte::page')

@section('title', 'Tecnologia Uzzipay')

@section('content_header')
    <h1 class="m-0 text-dark"><i></i></h1>
    <br>
@stop

@section('content')
    @include('layouts.notificacoes')

    <div class="container-fluid">
        <div class="container-fluid">
            <div class="row mb-4">
                @php
                    // Novos cards com os dados de $resultadoCards
                    $novosCards = [
                        ['title' => 'Total de Lotes Imp.', 'value' => number_format($resultadoCards->total_lotes_cadastrados ?? 0, 0, ',', '.'), 'icon' => 'fas fa-print', 'color' => 'purple', 'link' => route('inventario.cartao.visualizarRelatorio', ['tipo' => 'status-lotes'])],
                        ['title' => 'Total de Impressões', 'value' => number_format($resultadoCards->total_impressoes_cadastradas ?? 0, 0, ',', '.'), 'icon' => 'fas fa-id-card', 'color' => 'teal', 'link' => route('inventario.cartao.visualizarRelatorio', ['tipo' => 'volume-impressao-diario'])],
                        ['title' => 'Lotes Pendentes', 'value' => number_format($resultadoCards->lotes_pendentes ?? 0, 0, ',', '.'), 'icon' => 'fas fa-clock', 'color' => 'danger', 'link' => route('inventario.cartao.visualizarRelatorio', ['tipo' => 'status-lotes'])],
                        ['title' => 'Imp. 30 Dias', 'value' => number_format($resultadoCards->impressoes_ultimos_30_days ?? 0, 0, ',', '.'), 'icon' => 'fas fa-calendar-alt', 'color' => 'success', 'link' => route('inventario.cartao.visualizarRelatorio', ['tipo' => 'volume-impressao-diario'])],
                        ['title' => 'Média Imp. Lote', 'value' => number_format($resultadoCards->media_impressoes_por_lote ?? 0, 2, ',', '.'), 'icon' => 'fas fa-balance-scale', 'color' => 'orange', 'link' => route('inventario.cartao.visualizarRelatorio', ['tipo' => 'media-impressoes-por-lote'])],
                        ['title' => 'Total Clientes Imp.', 'value' => number_format($resultadoCards->total_clientes_cadastrados ?? 0, 0, ',', '.'), 'icon' => 'fas fa-handshake', 'color' => 'fuchsia', 'link' => route('inventario.cartao.visualizarRelatorio', ['tipo' => 'top-clientes-cartoes'])],
                    ];

                    // Mescla os dois arrays de cards
                    $allCards = array_merge($novosCards);
                @endphp

                @foreach($allCards as $card)
                    <div class="col-6 col-sm-4 col-md-2 mb-3">
                        <a href="{{ $card['link'] }}" class="text-decoration-none">
                            <div class="card bg-{{ $card['color'] }} text-white shadow rounded">
                                <div class="card-body text-center">
                                    <i class="{{ $card['icon'] }} fa-2x mb-2"></i>
                                    <h5 class="card-title">{{ $card['value'] }}</h5>
                                    <p class="card-text">{{ $card['title'] }}</p>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Relatórios Disponíveis</h4>
            </div>

            <table class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Relatório</th>
                        <th>Tipo</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Lista de todos os relatórios, incluindo os novos
                        $relatorios = [
                            ['nome' => 'Visão Geral do Status dos Lotes de Impressão', 'rota' => 'status-lotes', 'categoria' => 'Impressões'],
                            ['nome' => 'Detalhe de Lotes de Impressão com Contagem', 'rota' => 'detalhe-lotes-contagem', 'categoria' => 'Impressões'],
                            ['nome' => 'Impressões por Cliente e Grupo/Subgrupo', 'rota' => 'impressoes-cliente-subgrupo', 'categoria' => 'Impressões'],
                            ['nome' => 'Volume Diário de Impressão', 'rota' => 'volume-impressao-diario', 'categoria' => 'Impressões'],
                            ['nome' => 'Clientes com Maior Volume de Solicitação de Cartões', 'rota' => 'top-clientes-cartoes', 'categoria' => 'Impressões'],
                            ['nome' => 'Períodos de Maior Volume de Impressão (Anual e Mensal)', 'rota' => 'periodos-maior-impressao', 'categoria' => 'Impressões'],
                            ['nome' => 'Tipos de Combustível Mais Solicitados por Impressão', 'rota' => 'combustiveis-solicitados', 'categoria' => 'Impressões'],
                            ['nome' => 'Status de Impressão de Lotes por Cliente', 'rota' => 'status-lotes-por-cliente', 'categoria' => 'Impressões'],
                            ['nome' => 'Média de Impressões por Lote', 'rota' => 'media-impressoes-por-lote', 'categoria' => 'Impressões'],
                            ['nome' => 'Clientes com Maior Volume de Cartões Solicitados por Ano', 'rota' => 'clientes-cartoes-por-ano', 'categoria' => 'Impressões'],
                            ['nome' => 'Carros com Múltiplas Solicitações por Cliente', 'rota' => 'carros-multiplas-solicitacoes', 'categoria' => 'Impressões'],
                        ];
                    @endphp

                    @foreach($relatorios as $relatorio)
                        <tr>
                            <td>{{ $relatorio['nome'] }}</td>
                            <td><span class="badge bg-info">{{ $relatorio['categoria'] }}</span></td>
                            <td>
                                {{-- Link para Baixar PDF (usando a nova rota com 'acao' = 'baixar') --}}
                                <a href="{{ route('inventario.cartao.gerar', ['tipo' => $relatorio['rota'], 'acao' => 'baixar']) }}" target="_blank" class="btn btn-outline-success btn-sm" title="Baixar">
                                    <i class="fas fa-download"></i>
                                </a>
                                {{-- Link para Visualizar Relatório em HTML (usando a nova rota) --}}
                                <a href="{{ route('inventario.cartao.visualizarRelatorio', ['tipo' => $relatorio['rota']]) }}" target="_blank" class="btn btn-outline-primary btn-sm" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('footer')
    <div class="float-right d-none d-sm-inline" style="width: 100%">
        Equipe de Tecnologia
    </div>
    <strong><a href="https://rovemabank.com.br/">Rovema Bank</a> &copy; {{ date('Y') }} </strong> Todos os direitos reservados.
@endsection