@extends('adminlte::page')

@section('title', 'Credenciados')

@section('content_header')

    <h1 class="m-0 text-dark">Adicionar Lote</h1>

@stop

@section('content')

<div class="card card-primary">
    {{-- <div class="card-header">
      <h1 class="m-0 card-title">Adicionar Credenciado</h1>
    </div> --}}
    <!-- /.card-header -->
    <div class="card-body">
      <form action="{{ route('credenciado.store') }}" method="POST">
        @csrf
        <div class="row">
          <div class="col-sm-6">
            <!-- text input -->
            <div class="form-group">
              <label>numero</label>
              <input type="text" name="cnpj" class="form-control" pattern="\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}" placeholder="01.123.456/0001-00">


            </div>
          </div>
        </div>
        <div class="row">
            <div class="col-sm-6">

                <div class="form-group">
                    <label>Nome Fantasia</label>
                    <input type="text" name="nome_fantasia" class="form-control" placeholder="Uzzipay" >
                </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Razão Social</label>
                <input type="text" name="razao_social" class="form-control" placeholder="Uzzipay LTDA">
              </div>
            </div>
        </div>
        {{-- Endereço --}}
        <div class="row">
            <div class="col-sm-6">
              <!-- text input -->
              <div class="form-group">
                <label>CEP</label>
                <input type="text" name="cep" class="form-control" placeholder="76829-272">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Endereço</label>
                <input type="text" name="endereco" class="form-control" placeholder="Rua Abreu dos Anjos" >
              </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
              <!-- text input -->
              <div class="form-group">
                <label>Número</label>
                <input type="text" name="numero" class="form-control" placeholder="2765">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Bairro</label>
                <input type="text" class="form-control" name="bairro" placeholder="Solimões" >
              </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6">
              <!-- text input -->
              <div class="form-group">
                <label>Cidade</label>
                <input type="text" name="cidade" class="form-control" placeholder="Porto Velho">
              </div>
            </div>
            <div class="col-sm-6">
              <div class="form-group">
                <label>Estado</label>
                <input type="text" name="estado" class="form-control" placeholder="RO" >
              </div>
            </div>
        </div>
        {{-- selecionar produto --}}
        <div class="row">
            <div class="col-sm-6">
                <!-- Select multiple-->
                <div class="form-group">
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



      </form>
      <button type="submit" class="btn btn-primary">Adicionar</button>
  </div>
</div>


@stop
