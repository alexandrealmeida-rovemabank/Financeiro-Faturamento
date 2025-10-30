@extends('adminlte::page')
@section('title', 'Detalhes do Credenciado')

@section('content_header')
    <h1 class="m-0 text-dark">Detalhes do Credenciado: {{ $credenciado->nome }}</h1>
@stop

@section('content')
<div class="card card-primary card-outline card-outline-tabs">
    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="matriz-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="pill" href="#matriz-geral">Informações Gerais</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#matriz-taxas">Taxas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#matriz-tarifas">Tarifas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#matriz-terminais">POS</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#matriz-parametros">Parâmetros</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
            {{-- Aba Informações Gerais Matriz --}}
            <div class="tab-pane fade show active" id="matriz-geral">
                @include('admin.credenciados._info_gerais', ['empresa' => $credenciado])
            </div>

            {{-- Aba Taxas Matriz --}}
            <div class="tab-pane fade" id="matriz-taxas">
                @include('admin.credenciados._taxas', ['taxas' => $credenciado->taxas])
            </div>
            {{-- Aba Tarifas Matriz --}}
            <div class="tab-pane fade" id="matriz-tarifas">
                @include('admin.credenciados._tarifas', ['taxas' => $credenciado->taxas])
            </div>
            
            {{-- Aba Terminais Vinculados Matriz --}}
            <div class="tab-pane fade" id="matriz-terminais">
                @include('admin.credenciados._terminais_vinculados', ['terminais' => $credenciado->pos])
            </div>

            {{-- Aba Parâmetros Matriz --}}
            <div class="tab-pane fade" id="matriz-parametros">
                @include('admin.credenciados._parametros_form_credenciado', ['credenciado' => $credenciado])
            </div>
        </div>
    </div>
</div>

{{-- Acordeão Unidades --}}
@if($credenciado->unidades->isNotEmpty())
    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
        <h3>Unidades Vinculadas</h3>
        <div class="col-md-4">
            <input type="text" id="unidade-search" class="form-control" placeholder="Pesquisar unidade por Razão Social ou CNPJ...">
        </div>
    </div>

    <div class="accordion" id="unidadesAccordion">
        @foreach($credenciado->unidades as $unidade)
            <div class="card mb-2 shadow-sm unidade-card">
                <div class="card-header" id="heading-{{$unidade->id}}">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="mb-0 flex-grow-1">
                            <button class="btn btn-link btn-block text-left text-dark font-weight-bold" type="button" data-toggle="collapse" data-target="#collapse-{{$unidade->id}}" aria-expanded="false" aria-controls="collapse-{{$unidade->id}}">
                                <div class="row align-items-center">
                                    <div class="col-lg-1 col-md-2 text-muted"><strong>ID:</strong> {{ $unidade->id }}</div>
                                    <div class="col-lg-5 col-md-4 razao-social"><strong>Razão Social:</strong> {{ $unidade->razao_social }}</div>
                                    <div class="col-lg-4 col-md-4 cnpj"><strong>CNPJ:</strong> {{ $unidade->cnpj }}</div>
                                </div>
                            </button>
                        </h2>
                        <div class="pr-3">
                            {!! $unidade->ativo ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-danger">Inativo</span>' !!}
                        </div>
                    </div>
                </div>

                <div id="collapse-{{$unidade->id}}" class="collapse" aria-labelledby="heading-{{$unidade->id}}" data-parent="#unidadesAccordion">
                    <div class="card-body">
                        <div class="card card-primary card-outline card-outline-tabs">
                            <div class="card-header p-0 border-bottom-0">
                                <ul class="nav nav-tabs" id="unidade-{{$unidade->id}}-tabs" role="tablist">
                                    <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#unidade-{{$unidade->id}}-geral">Informações Gerais</a></li>
                                    <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#unidade-{{$unidade->id}}-taxas">Taxas</a></li>
                                    <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#unidade-{{$unidade->id}}-tarifas">Tarifas</a></li>
                                    <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#unidade-{{$unidade->id}}-terminais">POS</a></li>
                                    <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#unidade-{{$unidade->id}}-parametros">Parâmetros</a></li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="unidade-{{$unidade->id}}-geral">
                                        @include('admin.credenciados._info_gerais', ['empresa' => $unidade])
                                    </div>

                                    <div class="tab-pane fade" id="unidade-{{$unidade->id}}-taxas">
                                        @include('admin.credenciados._taxas', ['taxas' => $unidade->taxas->first()])
                                    </div>

                                    <div class="tab-pane fade" id="unidade-{{$unidade->id}}-tarifas">
                                        @include('admin.credenciados._tarifas', ['taxas' => $unidade->taxas->first()])
                                    </div>

                                     <div class="tab-pane fade" id="unidade-{{$unidade->id}}-terminais">
                                        @include('admin.credenciados._terminais_vinculados', ['terminais' => $unidade->pos])
                                    </div>

                                    <div class="tab-pane fade" id="unidade-{{$unidade->id}}-parametros">
                                        @include('admin.credenciados._parametros_form_credenciado', ['credenciado' => $unidade])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<a href="{{ route('credenciados.index') }}" class="btn btn-secondary mt-3">Voltar para a Lista</a>
@stop

@push('js')
<script>
$(document).ready(function(){
    $("#unidade-search").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#unidadesAccordion .unidade-card").filter(function() {
            var razaoSocial = $(this).find('.razao-social').text().toLowerCase();
            var cnpj = $(this).find('.cnpj').text().toLowerCase();
            $(this).toggle(razaoSocial.indexOf(value) > -1 || cnpj.indexOf(value) > -1)
        });
    });
});
</script>
@endpush
