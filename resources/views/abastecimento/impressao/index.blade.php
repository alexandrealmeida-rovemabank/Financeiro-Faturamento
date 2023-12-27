
@extends('adminlte::page')

@section('title', 'Credenciados')

@section('content_header')
    <h1 class="m-0 text-dark">Credenciados</h1>




@stop
@section('content')

    <div class="card">
        <div class="card-header">
            <a href="{{route('abastecimento.impressao.importar')}}" class="btn btn-primary">Importar</a>

        </div>

        <div class="card-body">
            <table id="tabela_lote" class="table table-striped" class="display">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Lote</th>
                        <th>Cliente</th>
                        <th>Data de importação</th>
                        <th>Data de modificação</th>
                        <th>status</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lote_impressao as $lotes)
                    <tr>
                        <td style="vertical-align: middle">{{ $lotes->id }}</td>
                        <td style="vertical-align: middle">{{ $lotes->lote }}</td>
                        <td style="vertical-align: middle">{{ $lotes->cliente }}</td>
                        <td style="vertical-align: middle">{{ $lotes->created_at}}</td>
                        <td style="vertical-align: middle">{{ $lotes->updated_at}}</td>
                        <td style="vertical-align: middle">{{ $lotes->status_impressao }}</td>
                        <td style="vertical-align: middle">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                  <li><a href="{{ route('.abastecimento.impressao.edit', $lotes->id) }}" class="dropdown-item" >Visualizar </a></li>
                                  <li><a class="dropdown-item" href="{{ route('abastecimento.impressao.edit.status', $lotes->id) }}" type="submit">Impresso</a></li>
                                  <li><a href="{{ route('abastecimento.impressao.lote.excluir', $lotes->id) }}" class="dropdown-item">Excluir</a></li>
                                </ul>
                              </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
@stop

@section('css')
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css"> --}}
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    {{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css"> --}}

@endsection


 @section('js')
    @parent
    {{-- <script>src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"</script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
    <script>
        new DataTable('#tabela_lote');
    </script>

    <script>
        $(document).ready(function () {
            $('#tabela_lote').DataTable({
                    "language": {
                    "search": "Pesquisar:",
                },
                dom: 'Bfrtip',
        buttons: [
        'copy', 'excel', 'pdf'
    ],

        });

        });



    </script>



@endsection


