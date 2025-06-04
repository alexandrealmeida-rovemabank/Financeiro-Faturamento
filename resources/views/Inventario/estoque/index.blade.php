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
            <!-- Cards Row -->
<div class="row mb-4">
        @php
            $cards = [
                ['title' => 'Credenciados', 'value' => $resultado->totalcredenciado, 'icon' => 'fas fa-users', 'color' => 'primary', 'link' => '/credenciado/index'],
                ['title' => 'Ativos', 'value' => $resultado->totalcredenciadoativo, 'icon' => 'fas fa-user-check', 'color' => 'success', 'link' => '/credenciado/index'],
                ['title' => 'Inativos', 'value' => $resultado->totalcredenciadoinativo, 'icon' => 'fas fa-user-times', 'color' => 'secondary', 'link' => '/credenciado/index'],
                ['title' => 'Estoque', 'value' => $resultado->totalestoque, 'icon' => 'fas fa-box', 'color' => 'info', 'link' => '/estoque/index'],
                ['title' => 'Estoque Op.', 'value' => $resultado->totalestoqueopercao, 'icon' => 'fas fa-dolly', 'color' => 'warning', 'link' => '/estoque/index'],
                ['title' => 'Lotes', 'value' => $resultado->totallote, 'icon' => 'fas fa-layer-group', 'color' => 'dark', 'link' => '/estoque/lote/index'],
            ];
        @endphp

        @foreach($cards as $card)
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
            @foreach([
                ['nome' => 'Relatório de Terminais por modelo', 'rota' => 'por-modelo'],
                ['nome' => 'Relatório de Terminais por Credenciado', 'rota' => 'por-credenciado'],
                ['nome' => 'Relatório de Terminais por Fabricante', 'rota' => 'por-fabricante'],
                ['nome' => 'Relatório de Terminais por Status', 'rota' => 'por-status'],
                ['nome' => 'Relatório de Terminais por Lote', 'rota' => 'por-lote'],
                ['nome' => 'Relatório de Terminais vinculados e desvinculados por Mês', 'rota' => 'por-vinculo-mes'],
                ['nome' => 'Relatório de Terminais por sistema', 'rota' => 'por-sistema'],
                ['nome' => 'Relatório de Terminais vinculados', 'rota' => 'por-status-vinculado'],
                ['nome' => 'Relatório de Terminais vinculados Grupo Rovema', 'rota' => 'por-status-vinculado-grupo-rovema'],
            ] as $relatorio)
                <tr>
                    <td>{{ $relatorio['nome'] }}</td>
                    <td><span class="badge bg-info">Estoque</span></td>
                    <td>
                        <a href="{{ url('/relatorio/'.$relatorio['rota'].'/baixar') }}" target="_blank" class="btn btn-outline-success btn-sm" title="Baixar">
                            <i class="fas fa-download"></i>
                        </a>
                        <a href="{{ url('/inventario/relatorio/'.$relatorio['rota'].'/visualizar') }}" target="_blank" class="btn btn-outline-primary btn-sm" title="Visualizar">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection

@section('footer')

    <!-- To the right -->
    <div class="float-right d-none d-sm-inline" style="width: 100%">
        Equipe de Tecnologia
    </div>
    <!-- Default to the left -->
    <strong><a href="https://rovemabank.com.br/">Rovema Bank</a> &copy; 2024 </strong> Todos os direitos reservados.

@endsection