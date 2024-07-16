@extends('adminlte::page')

@section('title', 'Credenciados')

@section('content_header')
    <h1 class="m-0 text-dark">Adicionar Credenciado</h1>
@stop

@section('content')
    @include('layouts.notificacoes')
    <div class="card card-primary">
        <div class="card-body">
            <form action="{{ route('credenciado.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-sm-6">
                        <!-- CNPJ -->
                        <div class="form-group">
                            <label>CNPJ*</label>
                            <input type="text" name="cnpj" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                            <div id="loading" style="display: none;">Carregando...</div>
                            <div id="error" style="display: none; color: red;"></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <!-- Nome Fantasia -->
                        <div class="form-group">
                            <label>Nome Fantasia*</label>
                            <input type="text" name="nome_fantasia" required class="form-control" oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <!-- Razão Social -->
                        <div class="form-group">
                            <label>Razão Social*</label>
                            <input type="text" name="razao_social" required class="form-control" oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                </div>
                {{-- Endereço --}}
                <div class="row">
                    <div class="col-sm-6">
                        <!-- CEP -->
                        <div class="form-group">
                            <label>CEP*</label>
                            <input type="text" name="cep" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <!-- Endereço -->
                        <div class="form-group">
                            <label>Endereço*</label>
                            <input type="text" name="endereco" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <!-- Número -->
                        <div class="form-group">
                            <label>Número*</label>
                            <input type="text" name="numero" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <!-- Bairro -->
                        <div class="form-group">
                            <label>Bairro*</label>
                            <input type="text" class="form-control" name="bairro" required oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <!-- Cidade -->
                        <div class="form-group">
                            <label>Cidade*</label>
                            <input type="text" name="cidade" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <!-- Estado -->
                        <div class="form-group">
                            <label>Estado*</label>
                            <input type="text" name="estado" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <!-- Telefone -->
                        <div class="form-group">
                            <label>Telefone</label>
                            <input type="text" name="telefone" class="form-control" oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <!-- Celular -->
                        <div class="form-group">
                            <label>Celular</label>
                            <input type="text" name="celular" class="form-control" oninput="this.value = this.value.toUpperCase()">
                        </div>
                    </div>
                </div>
                {{-- Selecionar produto --}}
                <div class="row">
                    <div class="col-sm-6">
                        <!-- Produtos -->
                        <div class="form-group" required>
                            <div class="form-check">
                                <input class="form-check-input" name="produto[]" type="checkbox" value="BIONIO">
                                <label class="form-check-label">BIONIO</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" name="produto[]" type="checkbox" value="ELIQ">
                                <label class="form-check-label">ELIQ</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" name="produto[]" type="checkbox" value="MAQUININHA">
                                <label class="form-check-label">MAQUININHA</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Adicionar</button>
            </form>
        </div>
    </div>
@stop

@section('js')
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
        $('input[name="cnpj"]').on('change', function() {
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
            var cnpj = $(this).val();
            let cnpjSemPontuacao = cnpj.replace(/[^\d]/g, '');
            $('#loading').show();
            $.ajax({
                url: '/buscar-cnpj/' + cnpjSemPontuacao,
                type: 'GET',
                success: function(data) {
                    showAlert('success', 'CNPJ encontrado na Receita!');
                    // Preencha os campos do formulário com os dados retornados
                    $('input[name="nome_fantasia"]').val(data.nome_fantasia);
                    $('input[name="razao_social"]').val(data.razao_social);
                    $('input[name="cep"]').val(data.cep);
                    $('input[name="endereco"]').val(data.logradouro);
                    $('input[name="numero"]').val(data.numero);
                    $('input[name="bairro"]').val(data.bairro);
                    $('input[name="cidade"]').val(data.municipio);
                    $('input[name="estado"]').val(data.uf);
                    $('input[name="telefone"]').val(data.ddd_telefone_1);
                    $('input[name="celular"]').val(data.ddd_telefone_2);
                    // Continue preenchendo os outros campos...
                    $('#loading').hide();
                },
                error: function(error) {
                    console.log(error);
                    showAlert('error', 'Houve algum erro ao buscar as informações do CNPJ, Por favor digite manualmente!');

                    // Esconder o elemento de carregamento
                    $('#loading').hide();
                }
            });
        });
    });
</script>
@endsection
