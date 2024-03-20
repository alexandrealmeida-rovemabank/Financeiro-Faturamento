@extends('adminlte::page')

@section('title', 'Credenciados')

@section('content_header')
    <h1 class="m-0 text-dark">Importar Lote</h1>




@stop
@section('content')
@include('layouts.notificacoes')


        <div class="card card-success">
        <div class="card-body">
            <form action="{{ route('abastecimento.impressao.processamento') }}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row">
                <div class="col-sm-6">
                  <!-- text input -->
                  <div class="form-group">
                    <label>Lote</label>
                    <input type="text" oninput="this.value = this.value.toUpperCase()" required name="lote" class="form-control" placeholder="LT00001">

                  </div>
                </div>
              </div>
              <div class="row">
                  <div class="col-sm-6">

                      <div class="form-group">
                          <label>Cliente</label>
                          <input type="text" name="cliente" oninput="this.value = this.value.toUpperCase()" required class="form-control" placeholder="Uzzipay" >
                      </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label>Arquivo xlsx</label>
                      <input type="file" name="arquivo" required oninput="this.value = this.value.toUpperCase()" class="form-control">
                    </div>
                  </div>
              </div>

              <button type="submit" class="btn btn-success">Processar</button>
            </form>
        </div>
        </div>
    </div>

@endsection

