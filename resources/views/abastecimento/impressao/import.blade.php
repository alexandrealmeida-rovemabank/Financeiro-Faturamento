@extends('adminlte::page')

@section('title', 'Importar Lote Cartões')

@section('content_header')
    <h1 class="m-0 text-dark">Importar Lote</h1>
@stop

@section('content')
@include('layouts.notificacoes')

<!-- Card para o formulário de importação de lote -->
<div class="card card-success">
    <div class="card-body">
        <!-- Formulário para processar o lote -->
        <form action="{{ route('abastecimento.impressao.processamento') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <!-- Campo para o nome do lote -->
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Lote</label>
                        <input type="text" oninput="this.value = this.value.toUpperCase()" required name="lote" class="form-control" placeholder="LT00001">
                    </div>
                </div>
            </div>
            <!-- Campos para cliente e arquivo -->
            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Cliente</label>
                        <input type="text" name="cliente" oninput="this.value = this.value.toUpperCase()" required class="form-control" placeholder="Uzzipay">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Arquivo xlsx</label>
                        <input type="file" name="arquivo" required oninput="this.value = this.value.toUpperCase()" class="form-control">
                    </div>
                </div>
            </div>
            <!-- Botão para submeter o formulário -->
            <button type="submit" class="btn btn-primary">Processar</button>
        </form>
    </div>
</div>

@endsection

@section('js')
<script>
    $(document).ready(function () {

        setTimeout(function() {
            $('#alert-success, #alert-error, #alert-warning').each(function() {
                $(this).animate({
                    marginRight: '-=1000',
                    opacity: 0
                }, 'slow', function() {
                    $(this).remove(); // Remove o elemento após a animação
                });
            });
        }, 5000);
    });
</script>
@endsection
