
@extends('adminlte::page')

@section('title', 'Lote de Cartões')

@section('content_header')
    <h1 class="m-0 text-dark">Lote de Cartões</h1>




@stop
@section('content')
@include('layouts.notificacoes')
    <div class="card">
        <div class="card-header">
            <a href="{{route('abastecimento.impressao.importar')}}" class="btn btn-primary">Importar</a>

        </div>

        <div class="card-body">
            <table id="loteCartoes" class="table table-striped" class="display">
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
                {{-- <tbody>
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
                                  @if($lotes->status_impressao == 'Importado')
                                    <li><a class="dropdown-item" href="{{ route('abastecimento.impressao.edit.status', $lotes->id) }}" type="submit">Impresso</a></li>
                                  @endif
                                  @if($lotes->status_impressao == 'Importado')
                                    <li><a href="{{ route('abastecimento.impressao.lote.excluir', $lotes->id) }}" class="dropdown-item">Excluir</a></li>
                                  @endif

                                </ul>
                              </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody> --}}

            </table>
        </div>
    </div>
@stop


@section('js')

<script>
    $(document).ready(function() {
        $('#loteCartoes').DataTable({
            "language": {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json',
            },
            processing: true,
            serverSide: true,
            ajax: '{{ route('abastecimento.impressao.index') }}',
            columns: [
                    { data: 'id', name: 'id' },
                    { data: 'lote', name: 'lote' },
                    { data: 'cliente', name: 'cliente'},
                    { data: 'created_at', name: 'created_at',
                    render: function(data, type, row) {
                        var dataObjeto = new Date(data);
                        return dataObjeto.toLocaleDateString('pt-BR') + ' ' + dataObjeto.toLocaleTimeString('pt-BR');
                        }
                    },
                    { data: 'updated_at', name: 'updated_at',
                    render: function(data, type, row) {
                       var dataObjeto = new Date(data);
                       return dataObjeto.toLocaleDateString('pt-BR') + ' ' + dataObjeto.toLocaleTimeString('pt-BR');
                       }
                   },
                    { data: 'status_impressao', name: 'status_impressao'},
                    { data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            "pageLength": 10,
            "lengthMenu": [10, 25, 50, 100, 200],

        });

    });
</script>
@endsection


