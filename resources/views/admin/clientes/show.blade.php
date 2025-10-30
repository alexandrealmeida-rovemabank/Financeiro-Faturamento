@extends('adminlte::page')

@section('title', 'Detalhes do Cliente')

@section('content_header')
    <h1 class="m-0 text-dark">Detalhes do Cliente: {{ $cliente->nome }}</h1>
@stop

@section('content')
    {{-- Card da Matriz --}}
    <div class="card card-primary card-outline card-outline-tabs">
        <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="matriz-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="matriz-geral-tab" data-toggle="pill" href="#matriz-geral" role="tab" aria-controls="matriz-geral" aria-selected="true">Informações Gerais</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="matriz-contratos-tab" data-toggle="pill" href="#matriz-contratos" role="tab" aria-controls="matriz-contratos" aria-selected="false">Contratos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="matriz-empenhos-tab" data-toggle="pill" href="#matriz-empenhos" role="tab" aria-controls="matriz-empenhos" aria-selected="false">Empenhos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="matriz-parametros-tab" data-toggle="pill" href="#matriz-parametros" role="tab" aria-controls="matriz-parametros" aria-selected="false">Parâmetros</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="matriz-tabs-content">
                {{-- Aba de Informações Gerais da Matriz --}}
                <div class="tab-pane fade show active" id="matriz-geral" role="tabpanel" aria-labelledby="matriz-geral-tab">
                    @include('admin.clientes._info_gerais', ['empresa' => $cliente])
                </div>

                {{-- Aba de Contratos da Matriz --}}
                <div class="tab-pane fade" id="matriz-contratos" role="tabpanel" aria-labelledby="matriz-contratos-tab">
                    @include('admin.clientes._contratos_table', ['contratos' => $cliente->contratos])
                </div>

                {{-- Aba de Empenhos da Matriz --}}
                <div class="tab-pane fade" id="matriz-empenhos" role="tabpanel" aria-labelledby="matriz-empenhos-tab">
                    @include('admin.clientes._empenhos_table', ['contratos' => $cliente->contratos])
                </div>

                {{-- Aba de Parâmetros da Matriz --}}
                <div class="tab-pane fade" id="matriz-parametros" role="tabpanel" aria-labelledby="matriz-parametros-tab">
                    @include('admin.clientes._parametros_form', ['empresa' => $cliente])
                </div>
            </div>
        </div>
    </div>

    {{-- Acordeão para as Unidades --}}
    @if($cliente->unidades->isNotEmpty())
        <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
            <h3>Unidades Vinculadas</h3>
            <div class="col-md-4">
                <input type="text" id="unidade-search" class="form-control" placeholder="Pesquisar unidade por Razão Social ou CNPJ...">
            </div>
        </div>
        <div class="accordion" id="unidadesAccordion">
            @foreach($cliente->unidades as $unidade)
                <div class="card mb-2 shadow-sm unidade-card">
                    <div class="card-header" id="heading-{{$unidade->id}}">
                        <div class="d-flex justify-content-between align-items-center">
                            {{-- Cabeçalho do Acordeão Melhorado --}}
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
                            {{-- Abas para cada Unidade com Layout Melhorado --}}
                            <div class="card card-primary card-outline card-outline-tabs">
                                <div class="card-header p-0 border-bottom-0">
                                    <ul class="nav nav-tabs" id="unidade-{{$unidade->id}}-tabs" role="tablist">
                                        <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#unidade-{{$unidade->id}}-geral">Informações Gerais</a></li>
                                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#unidade-{{$unidade->id}}-contratos">Contratos</a></li>
                                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#unidade-{{$unidade->id}}-empenhos">Empenhos</a></li>
                                        <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#unidade-{{$unidade->id}}-parametros">Parâmetros</a></li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <div class="tab-content">
                                        {{-- Aba de Informações Gerais da Unidade --}}
                                        <div class="tab-pane fade show active" id="unidade-{{$unidade->id}}-geral">
                                            @include('admin.clientes._info_gerais', ['empresa' => $unidade])
                                        </div>
                                        {{-- Aba de Contratos da Unidade --}}
                                        <div class="tab-pane fade" id="unidade-{{$unidade->id}}-contratos">
                                            @include('admin.clientes._contratos_table', ['contratos' => $unidade->contratos])
                                        </div>
                                        {{-- Aba de Empenhos da Unidade --}}
                                        <div class="tab-pane fade" id="unidade-{{$unidade->id}}-empenhos">
                                            @include('admin.clientes._empenhos_table', ['contratos' => $unidade->contratos])
                                        </div>
                                        {{-- Aba de Parâmetros da Unidade --}}
                                        <div class="tab-pane fade" id="unidade-{{$unidade->id}}-parametros">
                                            @include('admin.clientes._parametros_form', ['empresa' => $unidade])
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

    <a href="{{ route('clientes.index') }}" class="btn btn-secondary mt-3">Voltar para a Lista</a>
@stop

@push('js')
<script>
    // Script para o filtro de unidades
    $(document).ready(function(){
        $("#unidade-search").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#unidadesAccordion .unidade-card").filter(function() {
                // Procura tanto na razão social quanto no CNPJ dentro do cabeçalho do card
                var razaoSocial = $(this).find('.razao-social').text().toLowerCase();
                var cnpj = $(this).find('.cnpj').text().toLowerCase();
                $(this).toggle(razaoSocial.indexOf(value) > -1 || cnpj.indexOf(value) > -1)
            });
        });
    });
</script>
@endpush

