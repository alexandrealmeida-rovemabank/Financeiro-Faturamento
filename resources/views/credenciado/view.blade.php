@extends('adminlte::page')

@section('title', 'Credenciados')

@section('content_header')

    <h1 class="m-0 text-dark">Ficha do Credenciado</h1>

@stop

@section('content')
@include('layouts.notificacoes')
<div class="card-header-button">
    <a href="{{route('credenciado.edit', $credenciado->id)}}" class="btn btn-success">Editar</a>
    <a href="{{ route('credenciado.pdf', $credenciado->id) }}" id="gerarPDF" class="btn btn-success" target="_blank">PDF</a>

</div>
<div class="card">
    <div class="card-header">
      <h1 class="m-0 card-title text-dark">Informações</h1>
    </div>
    <div class="card-body">

        <div class="col-sm-6">
            <!-- radio -->
            <div class="form-group" style="display: block">
              <div class="form-check">
                <input class="form-check-input" type="radio" enabled="true" name="status" value="Ativo" @disabled(true)  @if ($credenciado->status=='Ativo') checked @endif>
                <label class="form-check-label">Ativo</label>
              </div>
              <div class="form-check">

                <input style="" class="form-check-input" type="radio" enabled="true" name="status"@disabled(true) value="Inativo"  @if ($credenciado->status=='Inativo') checked @endif>
                <label class="form-check-label">Inativo</label>
              </div>
             </div>
        </div>
        <div class="row">
          <div class="col-sm-6">
            <!-- text input -->
            <div class="form-group">
              <label>CNPJ: </label>
              <a  name="cnpj" pattern="\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}"> {{ $credenciado->cnpj_formatted }} </a>

            </div>
          </div>
        </div>
        <div class="row">
            <div class="col-sm-6">

                <div class="form-group">
                    <label>Nome Fantasia: </label>
                    <a name="nome_fantasia">{{ $credenciado->nome_fantasia }}</a>
                </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Razão Social: </label>
                <a>{{ $credenciado->razao_social }}</a>
              </div>
            </div>
        </div>
        {{-- Endereço --}}
        <div class="row">
            <div class="col-sm-6">
              <!-- text input -->
              <div class="form-group">
                <label>CEP: </label>
                <a>{{ $credenciado->cep }}</a>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Endereço: </label>
                <a>{{ $credenciado->endereco }}</a>
              </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
              <!-- text input -->
              <div class="form-group">
                <label>Número: </label>
                <a>{{ $credenciado->numero }}</a>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Bairro: </label>
                <a>{{ $credenciado->bairro }}</a>
              </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
              <!-- text input -->
              <div class="form-group">
                <label>Cidade: </label>
                <a>{{ $credenciado->cidade }}</a>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Estado: </label>
                <a>{{ $credenciado->estado}}</a>
              </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
              <!-- text input -->
              <div class="form-group">
                <label>Telefone: </label>
                <a >{{ $credenciado->telefone }}</a>
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Celular: </label>
                <a >{{ $credenciado->celular }}</a>
              </div>
            </div>
        </div>
        {{-- selecionar produto --}}
        <div class="row">
            <div class="col-sm-6">
                <!-- Select multiple-->
                <div class="form-group">
                    <label>Produtos: </label>
                    <?php
                        $produtosSelecionados = json_decode($credenciado->produto, true) ?? [];
                    ?>
                    <div class="form-check">
                        <input class="form-check-input" name="produto[]" @disabled(true) type="checkbox" value="BIONIO" {{ in_array('BIONIO', $produtosSelecionados) ? 'checked' : '' }}>
                        <label class="form-check-label">BIONIO</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" name="produto[] " @disabled(true) type="checkbox" value="ELIQ" {{ in_array('ELIQ', $produtosSelecionados) ? 'checked' : '' }}>
                        <label class="form-check-label">ELIQ</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" name="produto[]" @disabled(true) type="checkbox" value="MAQUININHA" {{ in_array('MAQUININHA', $produtosSelecionados) ? 'checked' : '' }}>
                        <label class="form-check-label">MAQUININHA</label>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="card">
    <div class="card-header">
      <h1 class="m-0 card-title text-dark">Terminais Vinculados</h1>
    </div>

    <div class="card-body">
        <table id="credenciados" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Terminal</th>
                    <th>Marca</th>
                    <th>Modedlo</th>
                    <th>Chip</th>
                    <th>Produto</th>
                    <th>Data de Vinculação</th>
                </tr>
            </thead>
             <tbody>
                 @foreach($terminal as $terminais)
                 @if ( $terminais->id_credenciado == $credenciado->id && $terminais->status =='Vinculado')
                <tr>
                    <td>{{ $terminais->id}}</td>
                    <td>{{ $terminais->estoque->numero_serie}}</td>
                    <td>{{ $terminais->estoque->fabricante }}</td>
                    <td>{{ $terminais->estoque->modelo }}</td>
                    <td>{{ $terminais->chip }}</td>
                    <td>{{ $terminais->produto }}</td>
                    <td>{{ $terminais->created_at}}</td>
                </tr>
                @endif
               @endforeach
            </tbody>

        </table>
    </div>


</div>
</div>

<br>
<br>
<br>


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
