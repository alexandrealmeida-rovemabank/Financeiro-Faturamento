@extends('adminlte::page')

@section('title', 'Correios - solicitação')

@section('content_header')

    <h1 class="m-0 text-dark">Detalhe Solicitação de Logística Reversa</h1>

@stop

@section('content')
@include('layouts.notificacoes')


{{-- contrato --}}
    <div class="card">
        <div class="card-header">
            <h1 class="m-0 card-title text-dark">Contrato</h1>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <label>Contrato:</label>
                    <div class="form-group" style="display:inline">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" enabled="true" name="status" value="Ativo" @disabled(true)  @if ($solicitacao->contrato =='05884660000104') checked @endif>
                            <label class="form-check-label">Uzipay Administradora de Convênios:</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" enabled="true" name="status" value="Ativo" @disabled(true)  @if ($solicitacao->contrato=='32192325000100') checked @endif>
                            <label class="form-check-label">Uzzipay Instituição de Pagamentos:</label>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- Select multiple-->
                    <div class="form-group">
                        <label>Serviço:</label>
                        @if ($solicitacao->servico == "03247")
                            <a>Sedex Reverso</a>
                        @elseif ($solicitacao->servico == "03301")
                            <a>Pac Reverso</a>
                        @endif
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Número do Cartão:</label>
                        <a>{{ $solicitacao->num_cartao }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- Select multiple-->
                    <div class="form-group">
                        <label>Numero da Coleta:</label>
                        <a>{{ $solicitacao->num_coleta }}</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Etiqueta:</label>
                        <a>{{ $solicitacao->num_etiqueta }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- Select multiple-->
                    <div class="form-group">
                        <label>Código Status Objeto:</label>
                        <a>{{ $solicitacao->status_objeto }}</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Descrição Status:</label>
                        <a>{{ $solicitacao->desc_status_objeto }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- Select multiple-->
                    <div class="form-group">
                        <label>Código Status Objeto:</label>
                        <a>{{ $solicitacao->data_solicitacao }}</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Descrição Status:</label>
                        <a>{{ $solicitacao->hora_solictacao }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
{{-- FIM CONTRATO --}}

{{-- Aki sera as atualizações via API do rastreamento --}}


{{-- Remetente --}}
    <div class="card">
        <div class="card-header">
            <h1 class="m-0 card-title text-dark">Remetente</h1>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                    <label>CNPJ:</label>
                    <a>{{ $solicitacao->cnpj_remetente }}</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Email:</label>
                        <a>{{ $solicitacao->email_remetente }}</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Nome Fantasia:</label>
                        <a>{{ $solicitacao->nome_fantasia_remetente }}</a>
                    </div>
                </div>
            </div>
            {{-- Endereço --}}
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                    <label>CEP:</label>
                    <a>{{ $solicitacao->cep_remetente }}</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Endereço:</label>
                    <a>{{ $solicitacao->logradouro_remetente }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                    <label>Número:</label>
                    <a>{{ $solicitacao->numero_remetente }}</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Bairro:</label>
                    <a>{{ $solicitacao->bairro_remetente }}</a>
                    </div>
                </div>
                 <div class="col-sm-6">
                    <div class="form-group">
                    <label>Complemento:</label>
                    <a>{{ $solicitacao->cidade_remetente }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                    <label>Cidade:</label>
                    <a>{{ $solicitacao->cidade_remetente }}</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Estado:</label>
                    <a>{{ $solicitacao->estado_remetente }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                    <label>DDD:</label>
                    <a>{{ $solicitacao->ddd_remetente }}</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Celular:</label>
                    <a>{{ $solicitacao->celular_remetente }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                    <label>DDD:</label>
                        <a>{{ $solicitacao->dddt_remetente }}</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Telefone:</label>
                        <a>{{ $solicitacao->telefone_remetente }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>


{{-- Final_remetente --}}


{{-- Destinatario --}}

    <div class="card">
        <div class="card-header">
            <h1 class="m-0 card-title text-dark">Destinatário</h1>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                <!-- text input -->
                <div class="form-group">
                    <label>CNPJ:</label>
                    <a>{{ $solicitacao->cnpj_destinatario }}</a>

                </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Email:</label>
                        <a>{{ $solicitacao->email_destinatario }}</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Nome Fantasia:</label>
                        <a>{{ $solicitacao->nome_fantasia_destinatario }}</a>
                    </div>
                </div>
            </div>
            {{-- Endereço --}}
            <div class="row">
                <div class="col-sm-6">
                <!-- text input -->
                <div class="form-group">
                    <label>CEP:</label>
                    <a>{{ $solicitacao->cep_destinatario }}</a>
                </div>
                </div>
                <div class="col-sm-6">
                <div class="form-group">
                    <label>Endereço:</label>
                    <a>{{ $solicitacao->logradouro_destinatario }}</a>
                </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                <!-- text input -->
                <div class="form-group">
                    <label>Número:</label>
                    <a>{{ $solicitacao->numero_destinatario }}</a>
                </div>
                </div>
                <div class="col-sm-6">
                <div class="form-group">
                    <label>Bairro:</label>
                    <a>{{ $solicitacao->bairro_destinatario }}</a>
                </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Complemento:</label>
                    <a>{{ $solicitacao->complemento_destinatario }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                <!-- text input -->
                <div class="form-group">
                    <label>Cidade:</label>
                    <a>{{ $solicitacao->cidade_destinatario }}</a>
                </div>
                </div>
                <div class="col-sm-6">
                <div class="form-group">
                    <label>Estado:</label>
                    <a>{{ $solicitacao->estado_destinatario }}</a>
                </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                <!-- text input -->
                <div class="form-group">
                    <label>DDD:</label>
                    <a>{{ $solicitacao->ddd_destinatario }}</a>
                </div>
                </div>
                <div class="col-sm-6">
                <div class="form-group">
                    <label>Celular:</label>
                    <a>{{ $solicitacao->celular_destinatario }}</a>
                </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                <!-- text input -->
                <div class="form-group">
                    <label>DDD:</label>
                    <a>{{ $solicitacao->dddt_destinatario }}</a>
                </div>
                </div>
                <div class="col-sm-6">
                <div class="form-group">
                    <label>Telefone: </label>
                    <a>{{ $solicitacao->telefone_destinatario }}</a>
                </div>
                </div>
            </div>


        </div>
    </div>
{{-- Final_destinatario --}}

{{-- Informações sobre a coleta  --}}
    <div class="card">
        <div class="card-header">
            <h1 class="m-0 card-title text-dark">Informações de Coleta</h1>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <!-- Select multiple-->
                    <div class="form-group">
                        <label>Tipo de Coleta:</label>
                        @if ($solicitacao->tipo_coleta == "CA")
                            <a>COLETA DOMICILIAR</a>
                        @elseif ($solicitacao->tipo_coleta == "03301")
                            <a>AUTORIZAÇÃO DE POSTAGEM</a>
                        @endif

                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Valor Declarado:</label>
                        <a>R$ {{ $solicitacao->valor_declarado }}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- Select multiple-->
                    <div class="form-group">
                        <label>AR - Aviso de Recebimento:</label>
                        @if ($solicitacao->ar == "0")
                            <a>NÃO</a>
                        @elseif ($solicitacao->ar == "1")
                            <a>SIM</a>
                        @endif

                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="form-group">
                        <label >Descrição do Objeto:</label>
                        <a  style="height: 200px">{{ $solicitacao->descricao_obj }}</a>

                      </div>
                </div>

            </div>
        </div>
    </div>







@stop

