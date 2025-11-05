@can('manage faturamento')
<div class="mb-3">
    <button class="btn btn-success" data-toggle="modal" data-target="#modalGerarFatura">
        <i class="fa fa-plus"></i> Gerar Nova Fatura
    </button>
</div>
@endcan

<table id="faturas-geradas-table" class="table table-bordered table-hover" style="width:100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nº Faturas</th>
            <th>NF-e</th>
            <th>Emissão</th>
            <th>Vencimento</th>
            <th>Valor Total</th>
            <th>Valor Impostos</th>
            <th>Valor Descontos</th>
            <th>Valor Líquido</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
    </thead>
</table>
