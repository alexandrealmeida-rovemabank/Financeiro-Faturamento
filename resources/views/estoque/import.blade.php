@extends('adminlte::page')

@section('title', 'Importar Ativos')

@section('content_header')
    <h1 class="m-0 text-dark">Importar Ativos</h1>

@stop
@section('content')

    <div class="card card-primary">
        <div class="card-body">
            <form action="{{route('estoque.processamento')}}" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="row">
                  <div class="col-sm-6">
                    <label>Lote</label>
                    <select class="form-control" name="id_lote" id="id_lote" >
                        @foreach ($lote as $estoques)
                            <option><a class="dropdown-item">{{ $estoques->lote }}</a></option>
                        @endforeach
                    </select>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group">
                      <label>Arquivo xlsx</label>
                      <input type="file" name="arquivo" class="form-control">
                    </div>
                  </div>
              </div>

              <button type="submit" class="btn btn-primary">Processar</button>
            </form>
        </div>
        </div>
    </div>

@endsection

