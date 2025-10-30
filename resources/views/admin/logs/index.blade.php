@extends('adminlte::page')

@section('title', 'Log de Atividades')

@section('content_header')
    <h1 class="fw-bold text-primary">
        <i class="fas fa-clipboard-list me-2"></i>Log de Atividades do Sistema
    </h1>
@stop

@section('content')
<div class="container-fluid">

    @include('layouts.notificacoes')
    @include('partials.session-messages')

    <div class="card card-filter mb-4 shadow-lg border-0">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="fas fa-filter me-2"></i>
            <h5 class="mb-0">Filtros</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="filtro-usuario" class="form-label">Usuário:</label>
                    <select id="filtro-usuario" class="form-control select2">
                        <option value="">Todos</option>
                        @foreach($users as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="filtro-data-inicio" class="form-label">Data/Hora Início:</label>
                    <input type="datetime-local" id="filtro-data-inicio" class="form-control">
                </div>

                <div class="col-md-3">
                    <label for="filtro-data-fim" class="form-label">Data/Hora Fim:</label>
                    <input type="datetime-local" id="filtro-data-fim" class="form-control">
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button id="btn-filtrar" class="btn btn-primary shadow-sm">
                        <i class="fas fa-search me-1"></i>Filtrar
                    </button>
                    <button id="btn-limpar" class="btn btn-secondary shadow-sm">
                        <i class="fas fa-eraser me-1"></i>Limpar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-filter mb-4 shadow-lg border-0">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="fas fa-list me-2"></i>
            <h5 class="mb-0">Atividades Registradas</h5>
        </div>

        <div class="card-body p-0">
            <table id="logs-table" class="table table-hover table-bordered mb-0 align-middle text-sm" style="width:100%">
                <thead class="table-light">
                    <tr class="text-secondary">
                        <th style="width: 5%">ID</th>
                        <th>Data/Hora</th>
                        <th>Usuário</th>
                        <th>Ação</th>
                        <th>Objeto</th>
                        <th>Detalhes (IP, etc.)</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@stop

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Cards */
    .card { border-radius: 12px; overflow: hidden; }
    .card-header { border-bottom: none; font-weight: 600; }

    /* Botões */
    .btn i { margin-right: 4px; }

    /* Tabela */
    table th, table td { vertical-align: middle !important; }
    table tbody tr:hover { background-color: #f8f9fa; }
    .badge { font-size: 0.85rem; }

    /* Forçar os botões do DataTables para a esquerda */
    div.dataTables_wrapper div.dt-buttons {
        float: left !important;
        margin-bottom: 10px;
    }

    div.dataTables_wrapper .dataTables_filter {
        float: right !important;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {

    $('.select2').select2({ width: '100%' });

    var table = $('#logs-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route('admin.logs.index') }}',
            data: function(d) {
                d.user_id = $('#filtro-usuario').val();
                d.data_inicio = $('#filtro-data-inicio').val();
                d.data_fim = $('#filtro-data-fim').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'created_at', name: 'created_at' },
            { data: 'causer_name', name: 'causer.name' },
            { data: 'description', name: 'description' },
            { data: 'subject_info', name: 'subject_id', orderable: false, searchable: false },
            { data: 'properties', name: 'properties', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        dom: '<"row mb-2"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="fas fa-file-excel me-1"></i>Excel', className: 'btn btn-success btn-sm shadow-sm' },
            { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf me-1"></i>PDF', className: 'btn btn-danger btn-sm shadow-sm', orientation: 'landscape', pageSize: 'A4' }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json' }
    });

    $('#btn-filtrar').on('click', function() { table.ajax.reload(); });
    $('#btn-limpar').on('click', function() {
        $('#filtro-usuario').val('').trigger('change');
        $('#filtro-data-inicio').val('');
        $('#filtro-data-fim').val('');
        table.ajax.reload();
    });

});
</script>
@endpush
