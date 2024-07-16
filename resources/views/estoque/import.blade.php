@extends('adminlte::page')

@section('title', 'Importar Ativos')

@section('content_header')
    <h1 class="m-0 text-dark">Importar Ativos</h1>
@stop

@section('content')
@include('layouts.notificacoes')

<div class="card card-success">
    <div class="card-body">
        <!-- Formulário para importação de ativos -->
        <form action="{{route('estoque.processamento')}}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-sm-6">
                    <!-- Campo de seleção de lote -->
                    <label>Lote</label>
                    <select class="form-control" required oninput="this.value = this.value.toUpperCase()" name="id_lote" id="id_lote">
                        @foreach ($lote as $estoques)
                            <option><a class="dropdown-item">{{ $estoques->lote }}</a></option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6">
                    <!-- Campo para upload de arquivo xlsx -->
                    <div class="form-group">
                        <label>Arquivo xlsx</label>
                        <input type="file" name="arquivo" required oninput="this.value = this.value.toUpperCase()" class="form-control">
                    </div>
                </div>
            </div>

            <!-- Botão para processar a importação -->
            <button type="submit" class="btn btn-success">Processar</button>
        </form>
    </div>
</div>

@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Máscara para valores em dinheiro
        $('.dinheiro').mask('#.##0,00', {reverse: true});

        // Remover alertas após 5 segundos
        setTimeout(function() {
            $('#alert-success, #alert-error, #alert-warning').each(function() {
                $(this).animate({
                    marginRight: '-=1000',
                    opacity: 0
                }, 'slow', function() {
                    $(this).remove();
                });
            });
        }, 5000);
    });
</script>
@endsection
