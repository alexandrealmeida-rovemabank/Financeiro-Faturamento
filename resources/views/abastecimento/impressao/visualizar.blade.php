@extends('adminlte::page')

@section('title', 'Credenciados')

@section('content_header')
    <h1 class="m-0 text-dark">Lote processado</h1>


@stop
@section('content')

<div class="card">


    <div class="card-body">
        <div class="card-header">
            {{-- <a href="{{route('index')}}" class="btn btn-primary">Importar</a> --}}

        </div>
        <table id="importação" class="table table-striped">
            <thead>
                <tr>
                    @foreach($header as $value)
                        <th>{{ $value }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $datas)
                <tr>
                    @foreach($datas as $value)
                        <td>{{ $value }}</td>
                    @endforeach
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
        new DataTable('#importação');
    </script>
@endsection

