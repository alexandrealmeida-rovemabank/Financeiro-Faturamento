@extends('adminlte::page')

@section('title', 'Correios - solicitação')

@section('content_header')
    <h1 class="m-0 text-dark">Solicitação de Logística Reversa</h1>
@stop

@section('content')
@include('layouts.notificacoes')

<form action="{{ route('logistica.correios.solicitarPostagemReversa') }}" method="POST">
    @csrf
{{-- contrato --}}
    <div class="card">
        <div class="card-header">
            <h1 class="m-0 card-title text-dark">Contrato</h1>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <label>Selecione o contrato*</label>
                    <div class="form-group" style="display:inline">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="contrato" value="05884660000104"  >
                            <label class="form-check-label">Uzipay Administradora de Convênios</label>
                        </div>
                        <div class="form-check">
                            <input style="" class="form-check-input" type="radio" name="contrato" value="32192325000100" >
                            <label class="form-check-label">Uzzipay Instituição de Pagamentos</label>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Produto*</label>
                        <select class="form-control search" name="produto" id="produto" >
                            <option >ELIQ</option>
                            <option >MAQUININHA</option>
                            <option >BIONIO</option>
                            <option >UZZIPAY</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- Select multiple-->
                    <div class="form-group">
                        <label>Serviço*</label>
                        <select class="form-control search" required name="servico" id="servico" >
                            <option value="03247">Sedex Reverso</option>
                            <option value="03301">Pac Reverso</option>
                        </select>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Número do Cartão*</label>
                        <select class="form-control search" required name="num_cartao" id="num_cartao">
                            <!-- Options will be dynamically populated here -->
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
{{-- FIM CONTRATO --}}
<!-- Spinner de carregamento para o remetente -->
<div id="loading-spinner-remetente" class="text-center" style="display: none;">
    <div class="spinner-border text-success" role="status">
        <span class="sr-only">Loading...</span>
    </div>
    <p class="mt-2">Carregando...</p>
</div>

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
                    <label>CNPJ*</label>
                        <input type="text" name="cnpj_remetente" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Email*</label>
                        <input type="email" name="email_remetente" class="form-control" required >
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Nome Fantasia*</label>
                        <input type="text" name="nome_fantasia_remetente" class="form-control" required oninput="this.value = this.value.toUpperCase()" >
                    </div>
                </div>
            </div>
            {{-- Endereço --}}
            <div class="row">
                <div class="col-sm-6">
                    <label for="cep_remetente">CEP*</label>
                    <div class="form-group d-flex">
                        <input type="text" id="cep_remetente" name="cep_remetente" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                        <a id="btn-consultar" class="btn btn-primary">Consultar</a>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Endereço*</label>
                    <input type="text" name="logradouro_remetente" class="form-control" required oninput="this.value = this.value.toUpperCase()" >
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                    <label>Número*</label>
                    <input type="text" name="numero_remetente" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Bairro*</label>
                    <input type="text" class="form-control" name="bairro_remetente" required oninput="this.value = this.value.toUpperCase()" >
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Complemento</label>
                        <input type="text" class="form-control" name="complemento_remetente" oninput="this.value = this.value.toUpperCase()">
                    </div>
                    </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                    <label>Cidade*</label>
                    <input type="text" name="cidade_remetente" class="form-control" required oninput="this.value = this.value.toUpperCase()" >
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>UF*</label>
                    <input type="text" name="estado_remetente" maxlength="2" class="form-control" required oninput="this.value = this.value.toUpperCase()"  >
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                    <label>DDD*</label>
                    <input type="text" name="ddd_remetente" class="form-control"  >
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Celular*</label>
                    <input type="text" name="celular_remetente" class="form-control"  >
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                    <!-- text input -->
                    <div class="form-group">
                    <label>DDD</label>
                    <input type="text" name="dddt_remetente" class="form-control"  >
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone_remetente" class="form-control"  >
                    </div>
                </div>
            </div>
        </div>
    </div>
{{-- Final_remetente --}}

<div id="loading-spinner-destinatario" class="text-center" style="display: none;">
    <div class="spinner-border text-success" role="status">
        <span class="sr-only">Loading...</span>
    </div>
    <p class="mt-2">Carregando...</p>
</div>
{{-- Destinatario --}}

    <div id="destinatario" class="card" style="display: none;">
        <div class="card-header">
            <h1 class="m-0 card-title text-dark">Destinatário</h1>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                <!-- text input -->
                <div class="form-group">
                    <label>CNPJ*</label>
                    <input type="text" name="cnpj_destinatario" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Email*</label>
                        <input type="email" name="email_destinatario" class="form-control" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label>Nome Fantasia*</label>
                        <input type="text" name="nome_fantasia_destinatario" class="form-control" required oninput="this.value = this.value.toUpperCase()" >
                    </div>
                </div>
            </div>
            {{-- Endereço --}}
            <div class="row">
                <div class="col-sm-6">
                <!-- text input -->
                <div class="form-group">
                    <label>CEP*</label>
                    <input type="text" name="cep_destinatario" class="form-control" required oninput="this.value = this.value.toUpperCase()">

                </div>
                </div>
                <div class="col-sm-6">
                <div class="form-group">
                    <label>Endereço*</label>
                    <input type="text" name="logradouro_destinatario" class="form-control" required oninput="this.value = this.value.toUpperCase()" >
                </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                <!-- text input -->
                <div class="form-group">
                    <label>Número*</label>
                    <input type="text" name="numero_destinatario" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                </div>
                </div>
                <div class="col-sm-6">
                <div class="form-group">
                    <label>Bairro*</label>
                    <input type="text" class="form-control" name="bairro_destinatario" required oninput="this.value = this.value.toUpperCase()" >
                </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Complemento</label>
                    <input type="text" class="form-control" name="complemento_destinatario" oninput="this.value = this.value.toUpperCase()">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                <!-- text input -->
                <div class="form-group">
                    <label>Cidade*</label>
                    <input type="text" name="cidade_destinatario" class="form-control" required oninput="this.value = this.value.toUpperCase()" >
                </div>
                </div>
                <div class="col-sm-6">
                <div class="form-group">
                    <label>UF*</label>
                    <input type="text" name="estado_destinatario" maxlength="2" class="form-control" required oninput="this.value = this.value.toUpperCase()"  >
                </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                <!-- text input -->
                <div class="form-group">
                    <label>DDD*</label>
                    <input type="text" name="ddd_destinatario" class="form-control"  >
                </div>
                </div>
                <div class="col-sm-6">
                <div class="form-group">
                    <label>Celular*</label>
                    <input type="text" name="celular_destinatario" class="form-control"  >
                </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                <!-- text input -->
                <div class="form-group">
                    <label>DDD</label>
                    <input type="text" name="dddt_destinatario" class="form-control"  >
                </div>
                </div>
                <div class="col-sm-6">
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" name="telefone_destinatario" class="form-control"  >
                </div>
                </div>
            </div>


        </div>
    </div>
{{-- Final_destinatario --}}

{{-- Informações sobre a coleta  --}}
    <div id="coleta" class="card" style="display: none;">
        <div class="card-header">
            <h1 class="m-0 card-title text-dark">Informações de Coleta</h1>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-6">
                    <!-- Select multiple-->
                    <div class="form-group">
                        <label>Tipo de Coleta*</label>
                        <select class="form-control search" name="tipo_coleta" id="tipo_coleta" >

                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                    <label>Valor Declarado*</label>
                    <input type="number"  name="valor_declarado" class=" form-control" min="0.00" max="10000.00" style="display:inline-block" />
                    </div>
                </div>
            </div>
            <div class="row">

                <input type="hidden"  name="qtd_item" class="form-control" value="1" />


                <div class="col-sm-6">
                    <!-- Select multiple-->
                    <div class="form-group">
                        <label>AR - Aviso de Recebimento</label>
                        <select class="form-control search" name="ar" id="ar">
                            {{-- <option value="0">Não</option>
                            <option value="1">Sim</option> --}}
                        </select>
                    </div>
                </div>

                <div class="col-sm-6">

                    <div class="form-group">
                        <label>Seleione a caixa que o cliente poderá solicitar na coleta/postagem:</label>
                        <select class="form-control" required name="embalagem" id="tipo_embalagem" >


                        </select>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div class="form-floating">
                        <textarea class="form-control" placeholder="Descreva o objeto" name="descricao_obj" id="floatingTextarea2" style="height: 200px"></textarea>
                        <label for="floatingTextarea2">Descrição do Objeto</label>
                      </div>
                </div>


            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Salvar</button>
</form>

@stop
@section('js')
<script src="https://igorescobar.github.io/jQuery-Mask-Plugin/js/jquery.mask.min.js"></script>
<script>

function showAlert(type, message) {
    var alertId = '#alert-' + type + '-v2';
    var messageId = '#alert-' + type + '-message-v2';

    // Define o texto da mensagem
    $(messageId).text(message);

    // Mostra a notificação adicionando a classe de animação
    $(alertId).addClass('slide-out');
    $(alertId).css('display', 'flex');


    // Configura um timeout para remover a classe e esconder a notificação após 5 segundos
    setTimeout(function() {
        $(alertId).removeClass('slide-out');
        $(alertId).css('display', 'none');
    }, 5000); // 5000 milissegundos = 5 segundos
}

$(document).ready(function() {

    $('.dinheiro').mask('#.##0,00', {reverse: true});
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

        $('#btn-consultar').on('click', function() {
    var cep = $('input[name="cep_remetente"]').val().replace(/[^\d]/g, '');
    var codServico = $('input[name="cod_servico"]').val();
    var $select = $('#tipo_coleta');

    $('#loading-spinner-remetente').show();
    $.ajax({
        url: '/verificarColeta/' + cep + '/' + codServico,
        type: 'GET',
        success: function(response) {
            if (response.coletaDisponivel === true) {
                showAlert('success', 'Coleta Domiciliar e Autorização de postagem Disponível!');
                $('#destinatario').css('display', 'flex');
                $('#coleta').css('display', 'flex');
                $('#tipo_coleta').prop('disabled', false);
                var opcoes = [
                    { value: 'CA', text: 'Coleta Domiciliar' },
                    { value: 'A', text: 'Autorização de Postagem' }
                ];
                $select.empty();
                $.each(opcoes, function(index, opcao) {
                    $select.append($('<option></option>').val(opcao.value).text(opcao.text));
                });
            } else {
                showAlert('warning', 'Coleta não disponível. Apenas autorização de postagem.');
                $('#tipo_coleta').prop('disabled', false);
                $('#destinatario').css('display', 'flex');
                $('#coleta').css('display', 'flex');
                var opcoes = [
                    { value: 'A', text: 'Autorização de Postagem' }
                ];
                $select.empty();
                $.each(opcoes, function(index, opcao) {
                    $select.append($('<option></option>').val(opcao.value).text(opcao.text));
                });
            }

            // Dispara o evento change para que o valor seja processado automaticamente
            $select.trigger('change');
            $('#loading-spinner-remetente').hide();
        },
        error: function(error) {
            console.log(error);
            showAlert('error', 'Houve algum erro ao verificar a coleta.');
            $('#loading-spinner-remetente').hide();
        }
    });
});

$('#tipo_coleta').on('change', function() {
    var coleta = $(this).val();
    console.log(coleta);
    var $ar = $('#ar');

    if (coleta === 'CA') {
        var lista = [
            { value: '0', text: 'Não' }
        ];
        $ar.empty();
        $.each(lista, function(index, item) {
            $ar.append($('<option></option>').val(item.value).text(item.text));
        });
    } else {
        var lista = [
            { value: '0', text: 'Não' },
            { value: '1', text: 'Sim' }
        ];
        $ar.empty();
        $.each(lista, function(index, item) {
            $ar.append($('<option></option>').val(item.value).text(item.text));
        });
    }
});



    $('input[name="cnpj_remetente"]').on('change', function() {
        var cnpj = $(this).val().replace(/[^\d]/g, '');
        $('#loading-spinner-remetente').show();
        $.ajax({
            url: '/buscar-cnpj/' + cnpj,
            type: 'GET',
            success: function(data) {
                showAlert('success', 'CNPJ encontrado na Receita!');
                $('input[name="nome_fantasia_remetente"]').val(data.nome_fantasia);
                $('input[name="email_remetente"]').val(data.email);
                $('input[name="cep_remetente"]').val(data.cep);
                $('input[name="logradouro_remetente"]').val(data.logradouro);
                $('input[name="numero_remetente"]').val(data.numero);
                $('input[name="bairro_remetente"]').val(data.bairro);
                $('input[name="cidade_remetente"]').val(data.municipio);
                $('input[name="estado_remetente"]').val(data.uf);
                $('input[name="telefone_remetente"]').val(data.ddd);
                $('input[name="celular_remetente"]').val(data.ddd_telefone_1);
                $('#loading-spinner-remetente').hide();
            },
            error: function(error) {
                showAlert('error', 'Houve algum erro ao buscar as informações do CNPJ, Por favor digite manualmente!');
                $('#loading-spinner-remetente').hide();
                console.log(error);
            }
        });
    });

    $('input[name="cnpj_destinatario"]').on('change', function() {
        var cnpj = $(this).val().replace(/[^\d]/g, '');
        $('#loading-spinner-destinatario').show();
        $.ajax({
            url: '/buscar-cnpj/' + cnpj,
            type: 'GET',
            success: function(data) {
                showAlert('success', 'CNPJ encontrado na Receita!');
                $('input[name="nome_fantasia_destinatario"]').val(data.nome_fantasia);
                $('input[name="email_destinatario"]').val(data.email);
                $('input[name="cep_destinatario"]').val(data.cep);
                $('input[name="logradouro_destinatario"]').val(data.logradouro);
                $('input[name="numero_destinatario"]').val(data.numero);
                $('input[name="bairro_destinatario"]').val(data.bairro);
                $('input[name="cidade_destinatario"]').val(data.municipio);
                $('input[name="estado_destinatario"]').val(data.uf);
                $('input[name="telefone_destinatario"]').val(data.ddd);
                $('input[name="celular_destinatario"]').val(data.ddd_telefone_1);
                $('#loading-spinner-destinatario').hide();
            },
            error: function(error) {
                showAlert('error', 'Houve algum erro ao buscar as informações do CNPJ, Por favor digite manualmente!');
                $('#loading-spinner-destinatario').hide();
                console.log(error);
            }
        });
    });



    $('input[name="contrato"]').on('change', function() {
        var contratoSelecionado = $(this).val();
        var $numCartaoField = $('#num_cartao');
        $numCartaoField.empty();
        $.ajax({
            url: '/logistica/correios/buscarCartao/' + contratoSelecionado,
            type: 'GET',
            success: function(response) {
                $.each(response, function(index, numeroCartao) {
                    $numCartaoField.append($('<option></option>').attr('value', numeroCartao).text(numeroCartao));
                });
            },
            error: function(error) {
                console.log(error);
            }
        });
    });

    var embalagens = [
        { tipo: ' ', codigo: ' ', descricao: 'Não autorizado a aquisição de caixa' },
        { tipo: '0', codigo: '116600403', descricao: 'Caixa de Encomenda "B" (16x11x6 cm)' },
        { tipo: '0', codigo: '116600055', descricao: 'Caixa Encomenda 01 (18x13,5x9 cm)' },
        { tipo: '0', codigo: '116600063', descricao: 'Caixa Encomenda 02 (27x18x9 cm)' },
        { tipo: '2', codigo: '116600071', descricao: 'Caixa Encomenda 03 (27x22,5x13,5 cm)' },
        { tipo: '0', codigo: '116600080', descricao: 'Caixa Encomenda 04 (36x27x18 cm)' },
        { tipo: '2', codigo: '116600160', descricao: 'Caixa Encomenda 05 (54x36x27 cm)' },
        { tipo: '0', codigo: '116600179', descricao: 'Caixa Encomenda 06 (36x27x27 cm)' },
        { tipo: '0', codigo: '116600187', descricao: 'Caixa Encomenda 07 (36x28x4 cm)' },
        { tipo: '0', codigo: '765000660', descricao: 'Envelope Bolha Grande (20x28 cm)' },
        { tipo: '2', codigo: '765000652', descricao: 'Envelope Bolha Médio (21x18 cm)' },
        { tipo: '2', codigo: '765000644', descricao: 'Envelope SEDEX Plástico Grande (40x28 cm)' },
        { tipo: '0', codigo: '765000636', descricao: 'Envelope SEDEX Plástico Médio (35,3x25 cm)' }
    ];

    var selectEmbalagem = document.getElementById('tipo_embalagem');

    embalagens.forEach(function(embalagem) {
        var option = document.createElement('option');
        option.value = embalagem.tipo + '-' + embalagem.codigo + '-' + embalagem.descricao;
        option.textContent = embalagem.descricao;
        selectEmbalagem.appendChild(option);
    });
});
</script>
@endsection
