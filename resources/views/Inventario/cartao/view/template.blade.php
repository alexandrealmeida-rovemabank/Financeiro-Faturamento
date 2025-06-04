@extends('adminlte::page')

@section('title', $titulo)

@section('content')
<h1>{{ $titulo }}</h1>

<div id="export-buttons"></div>
<br><br>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div id="export-buttons"></div>

    <button id="btnExportarPdf" class="btn btn-danger">
        <i class="fas fa-file-pdf"></i> Exportar PDF
    </button>
</div>
<div class="card">
    <div class="card-header">

        @if($filtros ?? false)
        <div class="row">
            @foreach($filtros as $filtro)
            <div class="col-sm-2">
                <div class="form-group">
                    <label for="filtro-{{ $filtro['campo'] }}">{{ $filtro['label'] }}: </label>
                    <select class="form-control filtro" id="filtro-{{ $filtro['campo'] }}"
                        data-coluna="{{ $filtro['coluna'] }}">
                        <option value="">Todos</option>
                        @foreach($filtro['opcoes'] as $opcao)
                        <option value="{{ $opcao }}">{{ $opcao }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    <div class="card-body">
        <table id="tabelaRelatorio" class="table table-striped">
            <thead>
                <tr>
                    @foreach($colunas as $coluna)
                    <th>{{ ucfirst($coluna) }}</th>
                    @endforeach
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {

    var table = $('#tabelaRelatorio').DataTable({
        lengthMenu: [
            [10, 25, 50, 100, 200, -1],
            [10, 25, 50, 100, 200, 'Todos'],
        ],
        dom: 'lBfrtip',
        buttons: ['csv', 'excel'],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json',
        },
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ $urlDados }}',
            data: function(d) {
                $('.filtro').each(function() {
                    var coluna = $(this).data('coluna');
                    d['filtros[' + coluna + ']'] = $(this).val();
                });
            }
        },
        columns: [
            @foreach($colunas as $col) {
            data: '{{ $col }}',
            name: '{{ $col }}'
            },
            @endforeach
        ],
        pageLength: 10,
        initComplete: function() {
            $('#export-buttons').append($('.dt-buttons'));
        }
    });

    $('.filtro').on('change', function() {
        table.draw();
    });

    $('#btnExportarPdf').on('click', function() {
    let filtros = {};

    $('.filtro').each(function() {
        let coluna = $(this).data('coluna');
        let valor = $(this).val();
        filtros[coluna] = valor;
    });

    let queryString = $.param({ filtros: filtros });

    window.location.href = '{{ route('inventario.exportarPdf', ['tipo' => $tipo]) }}' + '?' + queryString;
});

});
</script>
@endsection
