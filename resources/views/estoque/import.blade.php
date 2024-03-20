@extends('adminlte::page')

@section('title', 'Importar Ativos')

@section('content_header')
    <h1 class="m-0 text-dark">Importar Ativos</h1>

@stop
@section('content')
@include('layouts.notificacoes')

    <div class="card card-success">
        <div class="card-body">
            <form action="{{route('estoque.processamento')}}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row">
                  <div class="col-sm-6">
                    <label>Lote</label>
                    <select class="form-control" required oninput="this.value = this.value.toUpperCase()" name="id_lote" id="id_lote" >
                        @foreach ($lote as $estoques)
                            <option><a class="dropdown-item">{{ $estoques->lote }}</a></option>
                        @endforeach
                    </select>
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

