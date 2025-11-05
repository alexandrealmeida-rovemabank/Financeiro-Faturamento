<div class="mb-3">
    <button class="btn btn-info"><i class="fa fa-file-pdf"></i> Exportar PDF Detalhado</button>
    <button class="btn btn-primary"><i class="fa fa-file-excel"></i> Exportar XLS</button>
</div>

<table id="transacoes-table" class="table table-bordered table-hover" style="width:100%">
    <thead>
        <tr>
            <th>ID</th>
            <th>Faturada?</th>
            <th>Data</th>
            <th>Credenciado</th>
            <th>Grupo</th>
            <th>Subgrupo</th>
            <th>Produto</th>
            <th>Vl. Bruto</th>
            <th>Alíquota IR</th> {{-- NOVO (Req 8) --}}
            <th>Valor IR</th> {{-- NOVO (Req 8) --}}
            <th>Placa</th>
        </tr>
    </thead>
</table>