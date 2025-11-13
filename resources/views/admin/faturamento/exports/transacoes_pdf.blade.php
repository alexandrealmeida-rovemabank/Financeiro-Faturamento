<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório de Transações - {{ $cliente->razao_social }}</title>
    
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8px;
            color: #212121;
            margin: 0;
            padding: 0;
            /* Adiciona as margens laterais ao corpo principal */
            margin-left: 40px;
            margin-right: 40px;
        }
        
        .w-100 { width: 100%; }
        .text-right { text-align: right; }
        .text-blue { color: #004AE6; }
        .text-dark { color: #0D132B; }
        .text-muted { color: #888; }
        
        .summary-box {
            background-color: #E5EEFF;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .summary-box td {
            padding: 5px;
            vertical-align: top;
        }

                /* --- Tabela de Itens/Transações --- */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px; /* Reduzido */
        }
        .items-table th,
        .items-table td {
            padding: 6px 5px; /* Padding reduzido */
            text-align: left;
            border-bottom: 1px solid #E5EEFF;
        }
        .items-table th {
            background-color: #004AE6;
            color: #FFFFFF;
            font-size: 9px;
            text-transform: uppercase;
        }
        
        /* --- Tabela de Totais Financeiros --- */
        .totals-table {
            width: 40%;
            margin-left: 60%;
            margin-top: 15px;
        }
        .totals-table td {
            padding: 5px 10px;
            font-size: 10px; /* Aumentado um pouco */
        }
        .totals-table .total-final-row td {
            font-size: 14px;
            font-weight: bold;
            color: #004AE6;
            border-top: 2px solid #0D132B;
        }
    </style>
</head>
<body>

    <main>
        
        <div class="summary-box">
            <table class="w-100">
                <tr>
                    <td>
                        <span class="text-muted">CLIENTE</span><br>
                        <strong class="text-dark" style="font-size: 14px;">{{ $cliente->razao_social }}</strong><br>
                    </td>
                    <td class="text-right">
                        <span class="text-muted">PERÍODO DE APURAÇÃO</span><br>
                        <strong class="text-dark" style="font-size: 14px;">{{ $periodo }}</strong>
                    </td>
                </tr>
            </table>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Faturada?</th>
                    <th>Data</th>
                    <th>Credenciado</th>
                    <th>Grupo</th>
                    <th>Subgrupo</th>
                    <th>Produto</th>
                    <th>Placa</th>
                    <th class="text-right">Vl. Bruto</th>
                    <th class="text-right">Alíquota IR</th>
                    <th class="text-right">Valor IR</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transacoes as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td>{{ $row->faturada_texto }}</td>
                    <td>{{ $row->data_formatada }}</td>
                    <td>{{ $row->credenciado_nome }}</td>
                    <td>{{ $row->grupo_nome }}</td>
                    <td>{{ $row->subgrupo_nome }}</td>
                    <td>{{ $row->produto_nome }}</td>
                    <td>{{ $row->placa }}</td>
                    <td class="text-right">{{ $row->valor_bruto }}</td>
                    <td class="text-right">{{ $row->aliquota_formatada }}</td>
                    <td class="text-right">{{ $row->valor_ir_calculado }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align: center;" class="text-muted">Nenhuma transação encontrada.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </main>

</body>
</html>