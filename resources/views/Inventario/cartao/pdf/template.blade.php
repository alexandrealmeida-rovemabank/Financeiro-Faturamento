<!DOCTYPE html>
<html>
<head>
    <title>{{ $titulo }}</title>
    <style>
        @page {
            margin: 100px 50px;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            text-transform: uppercase;
            /* color: #004ae6; Tom de verde */
        }
        header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 60px;
            text-align: left;
            line-height: 35px;
        }
        footer {
            position: fixed;
            bottom: -100px;
            left: -25px;
            right: 0;
            height: 50px;
            text-align: left;
            line-height: 35px;
            font-size: 13px;
            color:rgb(0, 0, 0);
        }
       table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }

        th {
            background-color: #004ae6;
            color: #ffffff;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 8px;
            border: 1px solid #ddd;
        }

        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .logo {
            width: 150px;
            margin-bottom: 10px;
        }
        h2{
           text-align: center;
           font-weight: bold;
           margin-top: 25px; /* Ajuste conforme necessário */
        }

        .footer .page:after{
            content:counter(page);
        }
       
    </style>
</head>
<body>
        
    <header>
        <img src="{{ public_path('vendor/adminlte/dist/img/Rovema Pay.png') }}" alt="Rovema Bank" class="logo">
    </header>

    <footer>
        <p> Gerado em {{ now()->format('d/m/Y H:i') }} </p>
    </footer>

    <main>
        <h2>{{ $titulo }}</h2>

        @if($tipo == 'por-modelo')
            <table>
                <thead>
                    <tr>
                        <th>Modelo</th>
                        <th>Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dados as $item)
                        <tr>
                            <td>{{ $item->modelo }}</td>
                            <td>{{ $item->quantidade }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif($tipo == 'por-lote')
            <table>
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dados as $item)
                        <tr>
                            <td>{{ $item->lote }}</td>
                            <td>{{ $item->quantidade }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif($tipo == 'por-status')
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dados as $item)
                        <tr>
                            <td>{{ $item->status }}</td>
                            <td>{{ $item->quantidade }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif($tipo == 'por-fabricante')
            <table>
                <thead>
                    <tr>
                        <th>Fabricante</th>
                        <th>Quantidade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dados as $item)
                        <tr>
                            <td>{{ $item->fabricante }}</td>
                            <td>{{ $item->quantidade }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif($tipo == 'por-credenciado')
            <table>
                <thead>
                    <tr>
                        <th>Razão Social</th>
                        <th>CNPJ</th>
                        <th>Quantidade Vinculado</th>
                        <th>Quantidade Desvinculado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dados as $item)
                        <tr>
                            <td>{{ $item->razao_social }}</td>
                            <td>{{ $item->cnpj }}</td>
                            <td>{{ $item->vinculado }}</td>
                            <td>{{ $item->desvinculado }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif($tipo == 'por-vinculo-mes')
            <table>
                <thead>
                    <tr>
                        <th>Mês/Ano</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dados as $item)
                        <tr>
                            <td>{{ $item->mes_ano }}</td>
                            <td>{{ $item->status }}</td>
                            <td>{{ $item->total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif($tipo == 'por-sistema')
            <table>
                <thead>
                    <tr>
                        <th>Sistema</th>
                        <th>Vinculado</th>
                        <th>Desvinculado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dados as $item)
                        <tr>
                            <td>{{ $item->sistema }}</td>
                            <td>{{ $item->vinculado }}</td>
                            <td>{{ $item->desvinculado }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif($tipo == 'por-status-vinculado')
            <table>
                <thead>
                    <tr>
                        <th>Razão Social</th>
                        <th>CNPJ</th>
                        <th>Número Série</th>
                        <th>Chip</th>
                        <th>Produto</th>
                        <th>Status</th>
                        <th>Data Criação</th>
                        <th>Data de Atualização</th>
                        <th>Sistema</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dados as $item)
                        <tr>
                            <td>{{ $item->razao_social }}</td>
                            <td>{{ $item->cnpj }}</td>
                            <td>{{ $item->numero_serie }}</td>
                            <td>{{ $item->chip }}</td>
                            <td>{{ $item->produto }}</td>
                            <td>{{ $item->status }}</td>
                            <td>{{ $item->created_at }}</td>
                            <td>{{ $item->updated_at }}</td>
                            <td>{{ $item->sistema }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif($tipo == 'por-status-vinculado-grupo-rovema')
            <table>
                <thead>
                    <tr>
                        <th>Razão Social</th>
                        <th>CNPJ</th>
                        <th>Número Série</th>
                        <th>Chip</th>
                        <th>Produto</th>
                        <th>Status</th>
                        <th>Data Criação</th>
                        <th>Data de Atualização</th>
                        <th>Sistema</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dados as $item)
                        <tr>
                            <td>{{ $item->razao_social }}</td>
                            <td>{{ $item->cnpj }}</td>
                            <td>{{ $item->numero_serie }}</td>
                            <td>{{ $item->chip }}</td>
                            <td>{{ $item->produto }}</td>
                            <td>{{ $item->status }}</td>
                            <td>{{ $item->created_at }}</td>
                            <td>{{ $item->updated_at }}</td>
                            <td>{{ $item->sistema }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </main>
</body>
</html>
