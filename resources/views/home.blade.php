@extends('adminlte::page')

@section('title', 'Tecnologia Uzzipay')


@section('content_header')

    <!-- <h1 class="m-0 text-dark"><i>Bem-Vindo, {{ $user->name }}</i></h1>
    <br> -->

@stop

@section('content')
    @include('layouts.notificacoes')


    <div class="container-fluid">
        <div class="container-fluid">
            <!-- Cards Row -->
            <div class="row mb-4">
                @php
                    $cards = [
                        ['title' => 'Credenciados', 'value' => $resultado->credenciado, 'icon' => 'fa-store', 'link' => '/credenciado/index', 'color' => 'primary'],
                        ['title' => 'Estoque', 'value' => $resultado->estoque, 'icon' => 'fa-box', 'link' => '/estoque/index', 'color' => 'info'],
                        ['title' => 'Lotes', 'value' => $resultado->lote, 'icon' => 'fa-boxes-stacked', 'link' => '/estoque/lote/index', 'color' => 'success'],
                        ['title' => 'Logísticas', 'value' => $resultado->postagem_reversa, 'icon' => 'fa-truck-moving', 'link' => '/logistica/correios/index', 'color' => 'warning'],
                        ['title' => 'Lote de Cartões', 'value' => $resultado->lote_impressao, 'icon' => 'fa-layer-group', 'link' => '/abastecimento/impressao/index', 'color' => 'danger'],
                        ['title' => 'Cartões', 'value' => $resultado->impressao, 'icon' => 'fa-credit-card', 'link' => '/abastecimento/impressao/index', 'color' => 'secondary']
                    ];
                @endphp

                @foreach($cards as $card)
                    <div class="col-md-2">
                        <div class="small-box bg-white elevation-3" style="border-radius:0px 20px 0px 20px;">
                            <div class="inner">
                                <h3>{{ number_format($card['value'], 0, ',', '.') }}</h3>
                                <p>{{ $card['title'] }}</p>
                            </div>
                            <div class="icon">
                                <i class="fas {{ $card['icon'] }}"></i>
                            </div>
                            <a href="{{ $card['link'] }}" class="small-box-footer">
                                Detalhes <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>



    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title text-dark" style="color: #333; font-weight: bold;">Estoque Por Status</h3>
                        <div class="card-tools">
                            <a href="#" class="btn btn-tool btn-sm">
                                <i class="fas fa-download"></i>
                            </a>
                            <a href="#" class="btn btn-tool btn-sm">
                                <i class="fas fa-bars"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0 scrollable-table">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Quantidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($estoquePorStatus as $item)
                                    <tr>
                                        <td>{{ $item->status }}</td>
                                        <td>{{ $item->quantidade }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-body scrollable-area">
                        <canvas class="graficos" id="graficoPolarArea"></canvas>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body scrollable-area">
                        <canvas class="graficos" id="graficoPolarAreaModelo"></canvas>
                    </div>
                </div>
            </div>


            <div class="col">
                <div class="card">
                    <div class="card-header border-0">
                        <h3 class="card-title text-dark" style="color: #333; font-weight: bold;">Estoque Por Lote</h3>
                        <div class="card-tools">
                            <a href="#" class="btn btn-tool btn-sm">
                                <i class="fas fa-download"></i>
                            </a>
                            <a href="#" class="btn btn-tool btn-sm">
                                <i class="fas fa-bars"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0 scrollable-table">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Quantidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($estoquePorLote as $item)
                                    <tr>
                                        <td>{{ $item->lote }}</td>
                                        <td>{{ $item->quantidade }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body" style="max-height: 300px; ">
                        <canvas id="graficoPizza" style="width: 100%; height: 100%;"></canvas>

                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body" style="max-height: 300px; ">
                        <canvas id="graficopizzacontrato"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body" style="max-height: 300px;">
                        <canvas class="graficos" id="graficoBarras" style="height: 100%; width: 100%; "></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <br>

@section('footer')

    <!-- To the right -->
    <div class="float-right d-none d-sm-inline" style="width: 100%">
        Equipe de Tecnologia
    </div>
    <!-- Default to the left -->
    <strong><a href="https://rovemabank.com.br/">Rovema Bank</a> &copy; 2024 </strong> Todos os direitos reservados.

@endsection



{{-- <div class="container-fluid">
        <div class="card-header">
            <h3 class="m-0 text-dark"><i></i></h3>
        </div>
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>150</h3>
                        <p>New Orders</p>
                    </div>

                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                 </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>53<sup style="font-size: 20px">%</sup></h3>
                        <p>Bounce Rate</p>
                    </div>
                        <div class="icon">
                            <i class="ion ion-stats-bars"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>44</h3>
                        <p>User Registrations</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person-add"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>65</h3>
                        <p>Unique Visitors</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

        </div>
    </div> --}}


@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var ctxPizza = document.getElementById('graficoPizza').getContext('2d');
        var ctxPizzaContrato = document.getElementById('graficopizzacontrato').getContext('2d');
        var ctxBarras = document.getElementById('graficoBarras').getContext('2d');
        var ctxPolarModelo = document.getElementById('graficoPolarAreaModelo').getContext('2d');
        var ctxPolar = document.getElementById('graficoPolarArea').getContext('2d');

        // Dados e rótulos para o gráfico de pizza
        var dadosPizza = @json($logisticasPorTipo->pluck('total'));
        var labelsPizza = @json($logisticasPorTipo->pluck('tipo_coleta'));

        labelsPizza = labelsPizza.map(function(label) {
            if (label === 'A') {
                return 'Aut. de Postagem';
            } else if (label === 'CA') {
                return 'Coleta Domiciliar';
            } else {
                return label;
            }
        });

        var optionsPizza = {
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Solicitações por Tipo',
                    font: {
                        size: 20
                    }
                },
                legend: {
                    position: 'left',
                    labels: {
                        font: {
                            size: 14
                        }
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutBounce',
                onProgress: function(animation) {},
                onComplete: function(animation) {}
            }
        };

        var chartPizza = new Chart(ctxPizza, {
            type: 'pie',
            data: {
                labels: labelsPizza,
                datasets: [{
                    data: dadosPizza,
                    backgroundColor: [
                        '#00D0FF',
                        '#004AE6',
                    ]
                }]
            },
            options: optionsPizza
        });

        var dadosPizzaContrato = @json($logisticasPorContrato->pluck('total'));
        var labelsPizzaContrato = @json($logisticasPorContrato->pluck('contrato'));

        labelsPizzaContrato = labelsPizzaContrato.map(function(label) {
            if (label === '05884660000104') {
                return 'Uzzipay Soluções';
            } else if (label === '32192325000100') {
                return 'Uzzipay IP';
            } else {
                return label;
            }
        });

        var optionsPizzaContrato = {
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Solicitações por Contrato',
                    font: {
                        size: 20
                    }
                },
                legend: {
                    position: 'left',
                    labels: {
                        font: {
                            size: 14
                        }
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutBounce',
                onProgress: function(animation) {},
                onComplete: function(animation) {}
            }
        };

        var chartPizzaContrato = new Chart(ctxPizzaContrato, {
            type: 'pie',
            data: {
                labels: labelsPizzaContrato,
                datasets: [{
                    data: dadosPizzaContrato,
                    backgroundColor: [
                        '#00D0FF',
                        '#004AE6',
                    ]
                }]
            },
            options: optionsPizzaContrato
        });

        var monthNames = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
            "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"
        ];
        var monthNumbers = @json($solicitacoesPorMes->pluck('mes'));
        var labels = monthNumbers.map(monthNumber => monthNames[monthNumber -
        1]); // Converta números de meses em nomes

        var dataBarras = {
            labels: labels,
            datasets: [{
                label: 'Quantidade de Solicitações',
                data: @json($solicitacoesPorMes->pluck('quantidade')),
                backgroundColor: [
                    '#004AE6'
                ],
                borderColor: [
                    '#00D0FF'
                ],
                borderWidth: 3
            }]
        };

        var config = {
            type: 'bar',
            data: dataBarras,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Solicitações Por Mês',
                        font: {
                            size: 20
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeOutBounce',
                    onProgress: function(animation) {},
                    onComplete: function(animation) {}
                }
            }
        };

        // Criação do gráfico de barras
        var chartBarras = new Chart(ctxBarras, config);


        var dadosAreaFabricante = @json($estoquePorFabricante->pluck('quantidade'));
        var labelsAreaFabricante = @json($estoquePorFabricante->pluck('fabricante'));

        // Dados e opções para o gráfico de área
        var dataArea = {
            labels: labelsAreaFabricante,
            datasets: [{
                label: 'Fabricante',
                data: dadosAreaFabricante

            }]
        };

        var configArea = {
            type: 'polarArea',
            data: dataArea,
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Estoque Por Fabricante',
                        font: {
                            size: 20
                        }
                    }
                },
            }

        };

        var chartArea = new Chart(ctxPolar, configArea);


        var dadosAreaModelo = @json($estoquePorModelo->pluck('quantidade'));
        var labelsAreaModelo = @json($estoquePorModelo->pluck('modelo'));

        // Dados e opções para o gráfico de área
        var dataAreaModelo = {
            labels: labelsAreaModelo,
            datasets: [{
                label: 'Modelo',
                data: dadosAreaModelo

            }]
        };

        var configAreaModelo = {
            type: 'polarArea',
            data: dataAreaModelo,
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Estoque Por Modelo',
                        font: {
                            size: 20
                        }
                    }
                },
            }

        };

        var chartAreaModelo = new Chart(ctxPolarModelo, configAreaModelo);

    // Solicitações Chart
    new Chart(document.getElementById('solicitacoesChart'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($solicitacoesPorMes->pluck('mes')->map(function($mes) {
                        return ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'][$mes - 1];
                    })) !!},
                    datasets: [{
                        label: 'Solicitações',
                        data: {!! json_encode($solicitacoesPorMes->pluck('quantidade')) !!},
                        backgroundColor: 'rgba(0, 123, 255, 0.7)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
    });
</script>
@endsection
