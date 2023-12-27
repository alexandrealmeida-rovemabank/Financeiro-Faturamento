@extends('adminlte::page')

@section('title', 'Credenciados')

@section('content_header')
    <h1 class="m-0 text-dark">Credenciados</h1>




@stop
@section('content')

    <div class="card">
        <div class="card-header">
            <a href="{{route('credenciado.create')}}" class="btn btn-primary">Adicionar</a>

            <div class="card-tools ml-auto">
                <div class="input-group input-group-sm" style="width: 250px;">
                    <input type="text" class="form-control" placeholder="Pesquisar">
                    <div class="input-group-append">
                        <span class="input-group-text" style="padding: 6px;"><i class="fas fa-search" style="font-size: 14px;"></i></span>
                    </div>
                </div>
            </div>
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
                        <td><a href="{{ route('credenciado.edit', $creden->id) }}" class="btn btn-primary">Editar</a></td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endsection

@section('js')
    <script>src="https://code.jquery.com/jquery-3.7.0.js"</script>
    <script>src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"</script>
    <script>src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"</script>

    <script>
        new DataTable('#credenciados');
    </script>
@endsection

