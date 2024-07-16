@extends('adminlte::page')

@section('title', 'Credenciados')

@section('content_header')
    <h1 class="m-0 text-dark">Adicionar Credenciado</h1>
@stop

@section('content')
<div class="card card-success">
    {{-- <div class="card-header">
      <h1 class="m-0 card-title">Adicionar Credenciado</h1>
    </div> --}}
    <!-- /.card-header -->
    <div class="card-body">
      <form action="{{route('credenciado.atualizar', [$credenciado->id]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="col-sm-6">
            <!-- Radio buttons para status -->
            <div class="form-group" style="display: block">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="status" value="Ativo" @if ($credenciado->status=='Ativo') checked @endif>
                <label class="form-check-label">Ativo</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="status" value="Inativo" @if ($credenciado->status=='Inativo') checked @endif>
                <label class="form-check-label">Inativo</label>
              </div>
             </div>
        </div>
        <div class="row">
          <div class="col-sm-6">
            <!-- Campo de entrada para CNPJ -->
            <div class="form-group">
              <label>CNPJ</label>
              <input type="text" name="cnpj" class="form-control" pattern="\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}" placeholder="01.123.456/0001-00" value="{{ $credenciado->cnpj_formatted }}" @disabled(true)>
            </div>
          </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <!-- Campo de entrada para Nome Fantasia -->
                <div class="form-group">
                    <label>Nome Fantasia</label>
                    <input type="text" name="nome_fantasia" class="form-control" placeholder="Uzzipay" value="{{ $credenciado->nome_fantasia }}">
                </div>
            </div>
            <div class="col-sm-6">
              <!-- Campo de entrada para Razão Social -->
              <div class="form-group">
                <label>Razão Social</label>
                <input type="text" name="razao_social" class="form-control" placeholder="Uzzipay LTDA" value="{{ $credenciado->razao_social }}">
              </div>
            </div>
        </div>
        {{-- Endereço --}}
        <div class="row">
            <div class="col-sm-6">
              <!-- Campo de entrada para CEP -->
              <div class="form-group">
                <label>CEP</label>
                <input type="text" name="cep" class="form-control" placeholder="76829-272" value="{{ $credenciado->cep }}">
              </div>
            </div>
            <div class="col-sm-6">
              <!-- Campo de entrada para Endereço -->
              <div class="form-group">
                <label>Endereço</label>
                <input type="text" name="endereco" class="form-control" placeholder="Rua Abreu dos Anjos" value="{{ $credenciado->endereco }}">
              </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
              <!-- Campo de entrada para Número -->
              <div class="form-group">
                <label>Número</label>
                <input type="text" name="numero" class="form-control" placeholder="2765" value="{{ $credenciado->numero }}">
              </div>
            </div>
            <div class="col-sm-6">
              <!-- Campo de entrada para Bairro -->
              <div class="form-group">
                <label>Bairro</label>
                <input type="text" class="form-control" name="bairro" placeholder="Solimões" value="{{ $credenciado->bairro }}">
              </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
              <!-- Campo de entrada para Cidade -->
              <div class="form-group">
                <label>Cidade</label>
                <input type="text" name="cidade" class="form-control" placeholder="Porto Velho" value="{{ $credenciado->cidade }}">
              </div>
            </div>
            <div class="col-sm-6">
              <!-- Campo de entrada para Estado -->
              <div class="form-group">
                <label>Estado</label>
                <input type="text" name="estado" class="form-control" placeholder="RO" value="{{ $credenciado->estado }}">
              </div>
            </div>
        </div>
        {{-- Selecionar produto --}}
        <div class="row">
            <div class="col-sm-6">
                <!-- Checkbox para seleção de produtos -->
                <div class="form-group">
                    <label>Produtos</label>
                    <?php
                        $produtosSelecionados = json_decode($credenciado->produto, true) ?? [];
                    ?>
                    <div class="form-check">
                        <input class="form-check-input" name="produto[]" type="checkbox" value="BIONIO" {{ in_array('BIONIO', $produtosSelecionados) ? 'checked' : '' }}>
                        <label class="form-check-label">BIONIO</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" name="produto[]" type="checkbox" value="ELIQ" {{ in_array('ELIQ', $produtosSelecionados) ? 'checked' : '' }}>
                        <label class="form-check-label">ELIQ</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" name="produto[]" type="checkbox" value="MAQUININHA" {{ in_array('MAQUININHA', $produtosSelecionados) ? 'checked' : '' }}>
                        <label class="form-check-label">MAQUININHA</label>
                    </div>
                </div>
            </div>
        </div>
        <!-- Botão para salvar alterações -->
        <button type="submit" class="btn btn-success">Salvar</button>
    </form>
</div>
</div>
@stop

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
