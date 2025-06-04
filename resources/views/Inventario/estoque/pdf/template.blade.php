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
            color: rgb(0, 0, 0);
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
        h2 {
            text-align: center;
            font-weight: bold;
            margin-top: 25px;
        }
        .footer .page:after {
            content: counter(page);
        }
    </style>
</head>
<body>
	<main>
   <header>
        <img src="{{ public_path('vendor/adminlte/dist/img/Rovema Pay.png') }}" alt="Rovema Bank" class="logo">
    </header>

    <footer>
        <p> Gerado em {{ now()->format('d/m/Y H:i') }} </p>
    </footer>
    <h2>{{ $titulo }}</h2>

    <table>
        <thead>
            <tr>
                @foreach($colunas as $coluna)
                    <th>{{ ucfirst(str_replace('_', ' ', $coluna)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($dados as $item)
                <tr>
                    @foreach($colunas as $coluna)
                        <td>{{ $item->$coluna ?? '-' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($colunas) }}" style="text-align: center;">Nenhum dado encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
	 </main>
</body>
</html>
