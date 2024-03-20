@extends('adminlte::page')

@section('title', 'Correios')

@section('content_header')
    <h1 class="m-0 text-dark">Rastreamento de Objeto</h1>
@stop

@section('content')

    @include('layouts.notificacoes')

    <div class="container-sm">
        <form id="rastrear-form" action="{{ route('logistica.correios.rastrear') }}" method="POST">
            @csrf
            <div class="input-group mb-3">
                <input type="text" name="cod_rastreio" id="cod_rastreio" class="form-control" placeholder="Digite o codigo de rastreio" aria-label="Recipient's username" aria-describedby="button-addon2">
                <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Rastrear</button>
            </div>
        </form>
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



    <script>
        document.getElementById('rastrear-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Evita que o formulário seja enviado normalmente

            document.getElementById('loading-spinner').style.display = 'block';

            // Obtém o valor do campo de código de rastreio
            var codigoRastreio = document.getElementById('cod_rastreio').value;

            // Faz uma requisição AJAX para buscar os resultados
            // Aqui você deve fazer a requisição para o seu backend que retorna os resultados
            // Assim que os resultados forem recebidos, a função de callback será chamada
            // Substitua a URL pela URL correta para fazer a requisição para o seu backend
            fetch('{{ route("logistica.correios.rastrear") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Obtém o token CSRF do meta tag
                },
                body: JSON.stringify({ cod_rastreio: codigoRastreio }) // Envia o código de rastreio no corpo da requisição
            })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Erro ao fazer a requisição');
                }
                return response.json();
            })
            .then(function(data) {
                // Exibe os resultados na página
                exibirResultados(data);
            })
            .catch(function(error) {
                console.error('Erro:', error);
            })
            .finally(function() {
                // Oculta o ícone de carregamento após a requisição ser concluída (com sucesso ou erro)
                document.getElementById('loading-spinner').style.display = 'none';
            });
        });

        function exibirResultados(result) {
            // Verifica se há resultados
            if (result && result.objetos && result.objetos.length > 0) {
                var listaResultados = document.getElementById('lista-resultados');
                listaResultados.innerHTML = ''; // Limpa a lista de resultados

                // Adiciona os resultados à lista
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

                // Exibe o resultado na página
                document.getElementById('resultado').style.display = 'block';
            } else {
                // Caso não haja resultados, oculta a seção de resultados
                document.getElementById('resultado').style.display = 'none';
            }
        }
    </script>

@endsection
