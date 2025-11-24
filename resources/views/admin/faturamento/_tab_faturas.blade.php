{{-- resources/views/admin/faturamento/_tab_faturas.blade.php --}}

<div class="row mb-2" id="card-pendente-geracao-wrapper" style="display: none;">
    <div class="col-md-12">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fa fa-exclamation-triangle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Valor Pendente de Geração (Aba Geral)</span>
                <span class="info-box-number" id="card-valor-pendente-geracao" style="font-size: 1.5rem;">...</span>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fa fa-file-invoice"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Qtd Faturas</span>
                <span class="info-box-number" id="card-qtd-faturas">...</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-secondary"><i class="fa fa-dollar-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Valor Gerado</span>
                <span class="info-box-number" id="card-valor-gerado">...</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fa fa-hand-holding-usd"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Valor Pago</span>
                <span class="info-box-number" id="card-valor-pago">...</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fa fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Valor Pendente (Das Geradas)</span>
                <span class="info-box-number" id="card-valor-pendente">...</span>
            </div>
        </div>
    </div>
</div>


<div class="mb-3 d-flex flex-wrap align-items-center">

    @can('create faturamento')
        <button class="btn btn-success mr-3 mb-2" data-toggle="modal" data-target="#modalGerarFatura">
            <i class="fa fa-plus"></i> Gerar Nova Fatura
        </button>
    @endcan

    <div class="ml-auto mb-2">
        @can('edit faturamento')
            <button class="btn btn-outline-primary btn-sm" id="btn-editar-observacao-massa" disabled>
                <i class="fa fa-edit"></i> Editar Observação
            </button>
        @endcan
        
        @can('delete faturamento')
            <button class="btn btn-outline-danger btn-sm" id="btn-excluir-selecionadas-massa" disabled>
                <i class="fa fa-trash"></i> Excluir
            </button>
        @endcan
    </div>
</div>


<table id="faturas-geradas-table" class="table table-bordered table-hover" style="width:100%">
    <thead>
        <tr>
            <th style="width: 10px;"><input type="checkbox" id="fatura-select-all"></th>
            <th>ID</th>
            <th>Nº Fatura</th>
            <th>NF-e</th>
            <th>Emissão</th>
            <th>Vencimento</th>
            <th>Valor Total</th>
            <th>Impostos</th>
            <th>Desc. (IR)</th>
            <th>Desc. Manuais</th>
            <th>Taxa Adm (%)</th>
            <th>Tipo Taxa</th>
            <th>Valor Taxa (R$)</th>
            <th>Valor Líquido</th>
            <th>Valor Recebido</th>
            <th>Saldo Pendente</th>
            <th>Status</th>
            <th style="width: 40px;">Ações</th>
        </tr>
    </thead>
</table>