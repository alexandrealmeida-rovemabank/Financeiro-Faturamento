@extends('adminlte::page')

@section('title', 'Correios - solicitação')

@section('content_header')

    <h1 class="m-0 text-dark">Solicitação de Logística Juma</h1>

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
                    <div id="error" style="display: none; color: red;"></div>
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
                    <!-- text input -->
                    <div class="form-group">
                    <label>CEP*</label>
                    <input type="text" name="cep_remetente" class="form-control" required oninput="this.value = this.value.toUpperCase()">
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

    <div class="card">
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
                    <div id="error" style="display: none; color: red;"></div>
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
    <div class="card">
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
                            <option value="CA">Coleta Domiciliar</option>
                            <option value="A">Autorização de Postagem</option>
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
                        <select class="form-control search" name="ar" id="ar" disabled >
                            <option value="0">Não</option>
                            <option value="1">Sim</option>
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

    <button type="submit" class="btn btn-success">Salvar</button>

</form>



@stop

@section('js')
<script src="https://igorescobar.github.io/jQuery-Mask-Plugin/js/jquery.mask.min.js"></script>
<script>

    $('.dinheiro').mask('#.##0,00', {reverse: true});
    $(document).ready(function() {
    $('input[name="cnpj_remetente"]').on('change', function() {
        var cnpj = $(this).val();
        let cnpjSemPontuacao = cnpj.replace(/[^\d]/g, '');
        $('#loading-spinner-remetente').show(); // ID único para o spinner de remetente
        $.ajax({
            url: '/buscar-cnpj/' + cnpjSemPontuacao,
            type: 'GET',
            success: function(data) {
                // Preencha os campos do formulário com os dados retornados
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
                // Continue preenchendo os outros campos...
                $('#loading').hide();
            },
            error: function(error) {
                console.log(error);
                $('#error').text('Não foi possível encontrar o CNPJ.').show();
            },
            complete: function() {
                $('#loading-spinner-remetente').hide(); // Esconder o spinner após a requisição AJAX
            }
        });
    });

});

$(document).ready(function() {
    $('input[name="cnpj_destinatario"]').on('change', function() {
        var cnpj = $(this).val();
        let cnpjSemPontuacao = cnpj.replace(/[^\d]/g, '');
        $('#loading-spinner-destinatario').show();
        $.ajax({
            url: '/buscar-cnpj/' + cnpjSemPontuacao,
            type: 'GET',
            success: function(data) {
                // Preencha os campos do formulário com os dados retornados
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
                // Continue preenchendo os outros campos...
                $('#loading').hide();
            },
            error: function(error) {
                console.log(error);
                $('#error').text('Não foi possível encontrar o CNPJ.').show();
            },
            complete: function() {
                $('#loading-spinner-destinatario').hide(); // Esconder o spinner após a requisição AJAX
            }

        });
    });
});



    $(document).ready(function() {
        $('#tipo_coleta').on('change', function() {
            var tipoColeta = $(this).val();
            var $arField = $('#ar');
            if (tipoColeta === 'CA') {
                $arField.val('0'); // Defina o valor padrão como "Não"
                $arField.prop('disabled', true);
            } else {
                $arField.prop('disabled', false);
            }
        });
    });



    $(document).ready(function() {
        $('input[name="contrato"]').on('change', function() {
            var contratoSelecionado = $(this).val();
            var $numCartaoField = $('#num_cartao');
            $numCartaoField.empty();
            // Limpe as opções existentes

            // Faça a solicitação AJAX para buscar os números de cartão com base no contrato selecionado
            $.ajax({
                url: '/logistica/correios/buscarCartao/'+ contratoSelecionado, // Substitua pelo caminho para o endpoint que retorna o nome fantasia
                type: 'GET',



                success: function(response) {
                console.log(response);
                // Adicione as novas opções ao campo de seleção de número de cartão
                $.each(response, function(index, numeroCartao) {
                    $numCartaoField.append($('<option></option>').attr('value', numeroCartao).text(numeroCartao));
                });
            },
            error: function(error) {
                console.log(error);
                // Trate os erros, se necessário
            }
        });
        });
    });


        // Dados da tabela de embalagens
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

        // Obtém o elemento select
        var selectEmbalagem = document.getElementById('tipo_embalagem');

        // Preenche o select com as opções
        embalagens.forEach(function(embalagem) {
            var option = document.createElement('option');
            option.value = embalagem.tipo + '-' + embalagem.codigo + '-' + embalagem.descricao;
            option.textContent = embalagem.descricao;
            selectEmbalagem.appendChild(option);
        });



</script>
@endsection
