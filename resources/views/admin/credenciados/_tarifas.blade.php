@if($taxas)
    <div class="row">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-exchange-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Taxa de Transferência (DOC/PIX)</span>
                    <span class="info-box-number">R$ {{ number_format($taxas->taxa_transferencia ?? 0, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-wrench"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Taxa de Manutenção (POS)</span>
                    <span class="info-box-number">R$ {{ number_format($taxas->taxa_manutencao ?? 0, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-secondary"><i class="fas fa-tablet-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Aluguel do POS </span>
                    <span class="info-box-number">
                        @if($taxas->aluguel)
                            R$ {{ number_format($taxas->preco ?? 0, 2, ',', '.') }} (Cobrado)
                        @else
                            Não Cobrado
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

@else
    <p class="text-muted">Nenhum registro de tarifas encontrado para este credenciado.</p>
@endif
