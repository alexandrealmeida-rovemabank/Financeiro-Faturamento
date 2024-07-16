@extends('adminlte::page')

@section('title', 'Credenciados')

@section('content_header')

    <h1 class="m-0 text-dark">Editar Credendiado</h1>

@stop

@section('content')
@include('layouts.notificacoes')

<div class="card">
    <div class="card-header">
      <h1 class="m-0 card-title text-dark">informações</h1>
    </div>
    <div class="card-body">
      <form action="{{route('credenciado.atualizar',[$credenciado->id]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="col-sm-6">
            <!-- radio -->
            <div class="form-group" style="display: block">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="status" value="Ativo"  @if ($credenciado->status=='Ativo') checked @endif>
                <label class="form-check-label">Ativo</label>
              </div>
              <div class="form-check">

                <input style="" class="form-check-input" type="radio" name="status" value="Inativo"  @if ($credenciado->status=='Inativo') checked @endif>
                <label class="form-check-label">Inativo</label>
              </div>
             </div>
        </div>
        <div class="row">
          <div class="col-sm-6">
            <!-- text input -->
            <div class="form-group">
              <label>CNPJ</label>
              <input type="text" name="cnpj" class="form-control" oninput="this.value = this.value.toUpperCase()" pattern="\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}" placeholder="01.123.456/0001-00" value="{{ $credenciado->cnpj_formatted }}" @disabled(true) >


            </div>
          </div>
        </div>
        <div class="row">
            <div class="col-sm-6">

                <div class="form-group">
                    <label>Nome Fantasia</label>
                    <input type="text" name="nome_fantasia" class="form-control" required oninput="this.value = this.value.toUpperCase()" value="{{ $credenciado->nome_fantasia }}" >
                </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Razão Social</label>
                <input type="text" name="razao_social" class="form-control" required oninput="this.value = this.value.toUpperCase()" value="{{ $credenciado->razao_social }}">
              </div>
            </div>
        </div>
        {{-- Endereço --}}
        <div class="row">
            <div class="col-sm-6">
              <!-- text input -->
              <div class="form-group">
                <label>CEP</label>
                <input type="text" name="cep" class="form-control" required oninput="this.value = this.value.toUpperCase()" value="{{ $credenciado->cep }}">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Endereço</label>
                <input type="text" name="endereco" class="form-control" required oninput="this.value = this.value.toUpperCase()" value="{{ $credenciado->endereco }}" >
              </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
              <!-- text input -->
              <div class="form-group">
                <label>Número</label>
                <input type="text" name="numero" class="form-control" required oninput="this.value = this.value.toUpperCase()" value="{{ $credenciado->numero }}">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Bairro</label>
                <input type="text" class="form-control" name="bairro" required oninput="this.value = this.value.toUpperCase()" value="{{ $credenciado->bairro }}">
              </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
              <!-- text input -->
              <div class="form-group">
                <label>Cidade</label>
                <input type="text" name="cidade" class="form-control" required oninput="this.value = this.value.toUpperCase()" value="{{ $credenciado->cidade }}">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Estado</label>
                <input type="text" name="estado" class="form-control" required oninput="this.value = this.value.toUpperCase()" value="{{ $credenciado->estado }}" >
              </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
              <!-- text input -->
              <div class="form-group">
                <label>Telefone</label>
                <input type="text" name="telefone" class="form-control"  value="{{ $credenciado->telefone }}">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Celular</label>
                <input type="text" name="celular" class="form-control"  value="{{ $credenciado->celular }}" >
              </div>
            </div>
        </div>
        {{-- selecionar produto --}}
        <div class="row">
            <div class="col-sm-6">
                <!-- Select multiple-->
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

        <button type="submit" class="btn btn-success">Salvar</button>
    </form>


    </div>
</div>

<div class="card">
    <div class="card-header">
      <h1 class="m-0 card-title text-dark">Terminais Vinculados</h1>
    </div>

    <div class="card-body">
        {{-- Ativar Terminal --}}

    <form action="{{route('terminal.vincular',[$credenciado->id]) }}" method="GET">
        @csrf
        <div class="row" id="row_vincular_terminal">
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="form-group">
                    <label>Terminal</label>
                    <select class="form-control search" name="id_estoque" id="id_estoque">
                        @foreach ($estoques as $estoque)
                            @if($estoque->categoria == 'TERMINAL' && $estoque->status == "Disponível" )
                                <option value="{{ $estoque->id }}">{{ $estoque->numero_serie }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="form-group">
                    <label>Chip</label>
                    <select class="form-control search" name="chip" id="chip" >
                        <option value="Sem Chip">Sem Chip</option>
                        @foreach ($estoques as $estoque)
                            @if($estoque->categoria == 'CHIP' && $estoque->status == "Disponível")
                                <option value="{{ $estoque->id }}">{{ $estoque->numero_serie }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="form-group">
                    <label>Produto</label>
                    <select class="form-control search" name="produto" id="produto">
                        @if (str_contains($credenciado->produto, 'BIONIO'))
                            <option value="BIONIO">BIONIO</option>
                        @endif
                        @if (str_contains($credenciado->produto, 'ELIQ'))
                            <option value="ELIQ">ELIQ</option>
                        @endif
                        @if (str_contains($credenciado->produto, 'MAQUININHA'))
                            <option value="MAQUININHA">MAQUININHA</option>
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="form-group">
                    <label>Sistemas</label>
                    <select class="form-control search" name="sistema" id="sistema">
                        @if (str_contains($credenciado->produto, 'BIONIO'))
                            <option value="INFOX">INFOX</option>
                        @endif
                        @if (str_contains($credenciado->produto, 'ELIQ'))
                            <option value="SIGYO">SIGYO</option>
                            <option value="LOGPAY">LOGPAY</option>
                        @endif
                        @if (str_contains($credenciado->produto, 'MAQUININHA'))
                            <option value="GSURF">GSURF</option>
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-12">
                <div class="form-group">
                    <button type="submit" class="btn btn-success mt-4">Adicionar</button>
                </div>
            </div>
        </div>
    </form>
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
                    <th>Sistema</th>
                    <th>Data de Vinculação</th>
                    <th>Ação</th>
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
                    <td>{{ $terminais->sistema }}</td>
                    <td>{{ $terminais->created_at}}</td>
                    <td> <a class="btn btn-success" href="{{ route('terminal.desvincular', $terminais->id) }}" type="submit">Desvincular</a> </td>
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
@section('css')

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('.search').select2();

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
