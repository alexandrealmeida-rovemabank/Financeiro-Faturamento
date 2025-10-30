@if($taxas)
    <div class="row">
        <div class="col-md-4">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-primary"><i class="fas fa-file-invoice-dollar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Taxa Padrão</span>
                    <span class="info-box-number">{{ number_format($taxas->taxa_administrativa ?? 0, 2, ',', '.') }}%</span>
                </div>
            </div>
        </div>
    </div>

    <h5 class="mt-4 mb-2">Taxas Especiais (por Cliente)</h5>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Cliente Associado</th>
                    <th>CNPJ Cliente</th>
                    <th>Valor da Taxa (%)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($taxas->taxasEspeciais as $taxaEspecial)
                    <tr>
                        <td>{{ $taxaEspecial->cliente->nome ?? 'N/A' }}</td>
                        <td>{{ $taxaEspecial->cliente->cnpj ?? 'N/A' }}</td>
                        <td>{{ number_format($taxaEspecial->valor ?? 0, 2, ',', '.') }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">Nenhuma taxa especial configurada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h5 class="mt-4 mb-2">Multitaxas (Faixas)</h5>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Faixa (R$) Min</th>
                    <th>Faixa (R$) Max</th>
                    <th>Taxa Credenciado (%)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($taxas->multitaxas as $multitaxa)
                    <tr>
                        <td>R$ {{ number_format($multitaxa->cliente_tax_min ?? 0, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($multitaxa->cliente_tax_max ?? 0, 2, ',', '.') }}</td>
                        <td>{{ number_format($multitaxa->taxa_credenciado ?? 0, 2, ',', '.') }}%</td>
                    </tr>
                @empty
                     <tr><td colspan="3" class="text-center">Nenhuma multitaxa configurada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@else
    <p class="text-muted">Nenhum registro de taxas encontrado para este credenciado.</p>
@endif
