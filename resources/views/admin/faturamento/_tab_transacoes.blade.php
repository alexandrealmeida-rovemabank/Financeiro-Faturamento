{{-- resources/views/admin/faturamento/_tab_transacoes.blade.php --}}

<div class="mb-3">
    <a href="{{ route('faturamento.exportPDF', ['cliente_id' => $cliente->id, 'periodo' => $periodo]) }}" target="_blank" class="btn btn-info">
        <i class="fa fa-file-pdf"></i> Exportar PDF
    </a>
    <a href="{{ route('faturamento.exportXLS', ['cliente_id' => $cliente->id, 'periodo' => $periodo]) }}" target="_blank" class="btn btn-primary">
        <i class="fa fa-file-excel"></i> Exportar XLS (Completo)
    </a>
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
            <th>Alíquota IR</th>
            <th>Valor IR</th>
            <th>Placa</th>
        </tr>
    </thead>
</table>