@extends('adminlte::page')

@section('title', 'Credenciados')

@section('content_header')
    <h1 class="m-0 text-dark">Credenciados</h1>




@stop
@section('content')
@include('layouts.notificacoes')
    <div class="card">
        <div class="card-header">
            <a href="{{route('credenciado.create')}}" class="btn btn-primary">Adicionar</a>


        </div>

        <div class="card-body">
            <table id="credenciados" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome Fantasia</th>
                        <th>CNPJ</th>
                        <th>Produto</th>
                        <th>Status</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($credenciado as $creden)
                    <tr>
                        <td>{{ $creden->id }}</td>
                        <td>{{ $creden->nome_fantasia }}</td>
                        <td>{{ $creden->cnpj_formatted }}</td>
                        <td>{{ implode(' - ', json_decode($creden->produto)) }}</td>
                        <td>{{ $creden->status }}</td>
                        <td style="vertical-align: middle">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary dropdown-toggle " data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="{{ route('credenciado.edit', $creden->id) }}" class="dropdown-item">Visualizar</a></li>
                                    <li><a href="{{ route('credenciado.edit', $creden->id) }}" class="dropdown-item">Editar</a></li>
                                    @if($creden->status == 'Ativo')
                                        <li><a class="dropdown-item" href="{{ route('credenciado.edit', $creden->id) }}" type="submit">Inativar</a></li>
                                    @endif
                                    @if($creden->status == 'Importado')
                                        <li><a class="dropdown-item" href="{{ route('credenciado.edit', $creden->id) }}" type="submit">Ativar</a></li>
                                    @endif
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
@endsection

@section('js')
    {{-- <script>src="https://code.jquery.com/jquery-3.7.0.js"</script>
    <script>src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"</script>
    <script>src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"</script> --}}

    <script>
        new DataTable('#credenciados');
    </script>
        <script>
            $(document).ready(function () {
                $('#credenciados').DataTable({
                        "language": {
                        "search": "Pesquisar:",
                    },});
            });
        </script>
@endsection

