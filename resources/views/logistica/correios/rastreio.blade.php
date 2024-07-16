@extends('adminlte::page')

@section('title', 'Correios')

@section('content_header')
    <h1 class="m-0 text-dark">Rastreamento de Objeto</h1>
@stop

@section('content')

    @include('layouts.notificacoes')

    <div class="container-sm">
        <div class="input-group mb-3">
            <input type="text" name="cod_rastreio_input" id="cod_rastreio_input" class="form-control" placeholder="Digite o código de rastreio" aria-label="Recipient's username" aria-describedby="button-addon2">
            <button class="btn btn-outline-secondary" type="submit" onclick="buscarRastreamento()" id="button-addon2">Rastrear</button>
        </div>
    </div>

    <div id="loading-spinner" class="text-center" style="display: none;">
        <div class="spinner-border text-success" role="status">
            <span class="sr-only">Loading...</span>
        </div>
        <p class="mt-2">Carregando...</p>
    </div>

    <div id="resultado" class="card" style="display: none;">
        <div class="card-header">
            <h1 class="m-0 card-title text-dark">Resultado</h1>
        </div>

        <div class="card-body">
            <ul id="lista-resultados" class="list-group">
                <!-- Os resultados serão adicionados aqui dinamicamente -->
            </ul>
        </div>
    </div>

    <div class="card-body">
        <table id="table_rastreio" class="table table-striped display">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Remetente</th>
                    <th>Destinatário</th>
                    <th>Etiqueta</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pedidosPendentes as $pedidos)
                <tr>
                    <td>{{ $pedidos->id }}</td>
                    <td>{{ $pedidos->nome_fantasia_remetente }}</td>
                    <td>{{ $pedidos->nome_fantasia_destinatario }}</td>
                    <td class="cod_rastreio">{{ $pedidos->num_etiqueta }}</td>
                    <td>
                        <button type="button" class="btn btn-success" onclick="buscarRastreamentoTabela(this)">Rastrear</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection

@section('js')
<script>
    $(document).ready(function () {
        setTimeout(function() {
            $('#alert-success, #alert-error, #alert-warning').each(function() {
                $(this).animate({
                    marginRight: '-=1000',
                    opacity: 0
                }, 'slow', function() {
                    $(this).remove();
                });
            });
        }, 5000);

        $('#table_rastreio').DataTable({
            lengthMenu: [
                [10, 25, 50, 100, 200, -1],
                [10, 25, 50, 100, 200, 'Todos'],
            ],
            className: 'btn btn-success',
            "language": {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json',
            },
            drawCallback: function() {
                $('.dropdown-toggle').dropdown();
            }
        });
    });

    function buscarRastreamento() {
        var codigoRastreio = document.getElementById('cod_rastreio_input').value;
        if (!codigoRastreio) {
            alert('Por favor, insira um código de rastreio.');
            return;
        }

        document.getElementById('loading-spinner').style.display = 'block';

        $.ajax({
            url: '/rastrear_index/' + codigoRastreio,
            type: 'GET',
            success: function(response) {
                exibirResultados(response.result);
            },
            error: function(error) {
                console.log(error);
                alert('Houve algum erro ao buscar a coleta.');
            },
            complete: function() {
                document.getElementById('loading-spinner').style.display = 'none';
            }
        });
    }

    function buscarRastreamentoTabela(button) {
        var codigoRastreio = $(button).closest('tr').find('.cod_rastreio').text();
        if (!codigoRastreio) {
            alert('Código de rastreio não encontrado.');
            return;
        }

        document.getElementById('loading-spinner').style.display = 'block';

        $.ajax({
            url: '/rastrear_index/' + codigoRastreio,
            type: 'GET',
            success: function(response) {
                exibirResultados(response.result);
            },
            error: function(error) {
                console.log(error);
                alert('Houve algum erro ao buscar a coleta.');
            },
            complete: function() {
                document.getElementById('loading-spinner').style.display = 'none';
            }
        });
    }

    function exibirResultados(result) {
        if (result && result.objetos && result.objetos.length > 0) {
            var listaResultados = document.getElementById('lista-resultados');
            listaResultados.innerHTML = '';

            result.objetos.forEach(function(objeto) {
                var listItem = document.createElement('div');
                listItem.classList.add('card');
                listItem.classList.add('mb-3');

                var cardBody = document.createElement('div');
                cardBody.classList.add('card-body');

                var cardTitle = document.createElement('h5');
                cardTitle.classList.add('card-title');
                cardTitle.textContent = 'Informações da Encomenda';

                var cardText = document.createElement('p');
                cardText.classList.add('card-text');

                var codigoObjeto = document.createElement('p');
                codigoObjeto.innerHTML = '<strong>Código do Objeto:</strong> ' + objeto.codObjeto;

                var eventosList = document.createElement('ul');
                eventosList.classList.add('list-group');

                objeto.eventos.forEach(function(evento) {
                    var eventoItem = document.createElement('li');
                    eventoItem.classList.add('list-group-item');
                    eventoItem.innerHTML = '<strong>Evento:</strong> ' + evento.descricao + '<br>' +
                                            '<strong>Data e Hora do Evento:</strong> ' + evento.dtHrCriado + '<br>' +
                                            '<strong>Unidade:</strong> ' + evento.unidade.endereco.cidade + ', ' + evento.unidade.endereco.uf + '<br>';
                    eventosList.appendChild(eventoItem);
                });

                cardText.appendChild(codigoObjeto);
                cardText.appendChild(eventosList);
                cardBody.appendChild(cardTitle);
                cardBody.appendChild(cardText);
                listItem.appendChild(cardBody);
                listaResultados.appendChild(listItem);
            });
            document.getElementById('resultado').scrollIntoView({ behavior: 'smooth' });

            document.getElementById('resultado').style.display = 'block';
        } else {
            document.getElementById('resultado').style.display = 'none';
        }
    }

    function toggleResultado() {
        var body = document.getElementById('resultado-body');
        var icon = document.getElementById('toggle-icon');

        if (body.style.display === 'none') {
            body.style.display = 'block';
            icon.classList.remove('collapsed');
        } else {
            body.style.display = 'none';
            icon.classList.add('collapsed');
        }
    }
</script>
@endsection
