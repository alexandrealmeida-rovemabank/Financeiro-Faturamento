<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fatura {{ $fatura->numero_fatura }}</title>
    
    <style>
        /* --- REMOVIDO: @page, header, footer --- */

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8px;
            color: #212121;
            margin: 0;
            padding: 0;
        }
        

        /* --- ESTILOS DO CONTEÚDO PRINCIPAL --- */
        .w-100 { width: 100%; }
        .w-50 { width: 50%; }
        .w-40 { width: 40%; }
        .w-60 { width: 60%; }
        .mt-20 { margin-top: 20px; }
        .mb-10 { margin-bottom: 10px; }
        .mb-20 { margin-bottom: 20px; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .text-blue { color: #004AE6; }
        .text-dark { color: #0D132B; }
        .text-muted { color: #888; }
        
        .page-break-before {
            page-break-before: always;
        }
        
        /* --- Bloco de Resumo (Estilo Cartão) --- */
        .summary-box {
            background-color: #E5EEFF;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px; /* Reduzido */
        }
        .summary-box td {
            padding: 5px;
            vertical-align: top;
        }
        .summary-box .total-label {
            font-size: 10px;
            font-weight: bold;
            color: #004AE6;
        }
        .summary-box .total-value {
            font-size: 20px;
            font-weight: bold;
            color: #0D132B;
            letter-spacing: -1px;
        }


        .bank-data-box {
            border: 1px solid #E5EEFF;
            border-radius: 8px;
            padding: 10px 15px;
            margin-bottom: 20px;
            font-size: 9px;
            line-height: 1.4;
            page-break-inside: avoid; /* Evita quebra dentro da caixa */
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
        
        .totals-table {
            width: 40%;
            margin-left: 60%;
            margin-top: 15px;
            page-break-inside: avoid; /* Evita quebra dentro da caixa */
        }
        .totals-table td {
            padding: 5px 10px;
            font-size: 10px;
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
        
        <div class="bank-data-box">
            <strong>DADOS BANCÁRIOS PARA PAGAMENTO</strong><br>
            @if($paramGlobal)
                {{ $paramGlobal->razao_social ?? '' }} | CNPJ: {{ $paramGlobal->cnpj ?? '' }}<br>
                Banco: {{ $paramGlobal->banco }} | 
                Agência: {{ $paramGlobal->agencia }} | 
                C/C: {{ $paramGlobal->conta }} |
                Chave PIX (CNPJ): {{ $paramGlobal->chave_pix ?? '' }}
            @else
                Dados bancários não configurados.
            @endif
        </div>

        <div class="summary-box">
            <table class="w-100">
                <tr>
                    <td class="w-60">
                        <span class="text-muted">CLIENTE</span><br>
                        <strong class="text-dark" style="font-size: 14px;">{{ $fatura->cliente->razao_social }}</strong><br>
                        <span class="text-dark">CNPJ: {{ $fatura->cliente->cnpj }}</span><br>
                        <span class="text-dark">{{ $fatura->cliente->endereco_completo ?? 'Endereço não informado' }}</span><br>
                    </td>
                    <td class="w-40" style="text-align: right; border-left: 1px dashed #004AE6; padding-left: 15px;">
                        <span class="total-label">DATA DE VENCIMENTO</span><br>
                        <span class="total-value" style="font-size: 18px;">{{ $fatura->data_vencimento->format('d/m/Y') }}</span>
                        <br><br>
                        <span class="total-label">VALOR LÍQUIDO A PAGAR</span><br>
                        <span class="total-value">R$ {{ number_format($fatura->valor_liquido, 2, ',', '.') }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <table class="w-100 mb-20" style="font-size: 9px;">
            <tr>
                <td>
                    <strong>Data de Emissão:</strong> {{ $fatura->data_emissao->format('d/m/Y') }}
                </td>
                <td>
                    <strong>Nº Nota Fiscal:</strong> {{ $fatura->nota_fiscal ?? 'N/A' }}
                </td>
                <td class="text-right">
                    <strong>Período de Apuração:</strong> {{ $fatura->periodo_fatura->locale('pt_BR')->translatedFormat('F/Y') }}
                </td>
            </tr>
        </table>

        <h4 class="text-blue" style="margin-bottom: 5px;">Detalhamento das Transações</h4>
        <table class="items-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Credenciado</th>
                    <th>Grupo</th>
                    <th>Subgrupo</th>
                    <th>Produto</th>
                    <th>Placa</th>
                    <th class="text-right">Vl. Bruto</th>
                    <th class="text-right">Alíq. IR</th>
                    <th class="text-right">Vl. IR</th>
                </tr>
            </thead>
            <tbody>
                {{-- Lógica de quebra de página manual removida --}}
                @forelse($transacoes as $tr)
                    <tr>
                        <td>{{ $tr->id }}</td>
                        <td>{{ $tr->data }}</td>
                        <td>{{ $tr->credenciado }}</td>
                        <td>{{ $tr->grupo }}</td>
                        <td>{{ $tr->subgrupo }}</td>
                        <td>{{ $tr->produto }}</td>
                        <td>{{ $tr->placa }}</td>
                        <td class="text-right">{{ $tr->valor_bruto }}</td>
                        <td class="text-right">{{ $tr->aliquota_ir }}</td>
                        <td class="text-right">{{ $tr->valor_ir }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center;" class="text-muted">Nenhum item de faturamento encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td class="text-muted">Valor Bruto (Serviços):</td>
                <td class="text-right text-muted">R$ {{ number_format($fatura->valor_total, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-muted">Descontos (IR Retido):</td>
                <td class="text-right text-muted">R$ {{ number_format($fatura->valor_descontos, 2, ',', '.') }}</td>
            </tr>
           
            @if($fatura->taxa_adm_valor != 0)
            <tr>
                <td class="text-muted">Taxa Adm. ({{ number_format($fatura->taxa_adm_percent, 2, ',', '.') }}%):</td>
                <td class="text-right text-muted">R$ {{ number_format($fatura->taxa_adm_valor, 2, ',', '.') }}</td>
            </tr>
            @endif
            
            @if($totalDescontosManuais > 0)
            <tr>
                <td class="text-muted">Descontos Manuais:</td>
                <td class="text-right text-muted">R$ {{ number_format($totalDescontosManuais, 2, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-final-row">
                <td>Valor Líquido Total:</td>
                <td class="text-right">R$ {{ number_format($fatura->valor_liquido, 2, ',', '.') }}</td>
            </tr>
        </table>

        @if($fatura->descontos->count() > 0)
        <div class="mt-20" style="page-break-inside: avoid;">
            <strong class="text-dark">Justificativas dos Descontos Manuais:</strong>
            <ul style="font-size: 9px; margin-top: 5px; padding-left: 15px;">
                @foreach($fatura->descontos as $desconto)
                    <li>
                        R$ {{ number_format($desconto->valor, 2, ',', '.') }} 
                        (Aplicado por: {{ $desconto->usuario->name ?? 'N/A' }})
                        @if($desconto->justificativa)
                         - {{ $desconto->justificativa }}
                        @endif {{-- <--- ESTA É A CORREÇÃO --}}
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($fatura->observacoes)
        <div class="mt-20" style="page-break-inside: avoid;">
            <strong class="text-dark">Observações:</strong>
            <p style="font-size: 9px; margin-top: 5px; border: 1px solid #E5EEFF; padding: 10px; border-radius: 4px;">
                {!! nl2br(e($fatura->observacoes)) !!}
            </p>
        </div>
        @endif

    </main>

</body>
</html>