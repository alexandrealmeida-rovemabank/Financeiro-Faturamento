@extends('adminlte::page')

@section('title', 'Lote')

@section('content_header')
    <h1 class="m-0 text-dark">Acompanhamento de Atualização</h1>
@stop

@section('content')
@include('layouts.notificacoes')

    <div class="card">
        <div id="progresso-container">
            <ul id="progresso-list">
                <!-- Progresso será atualizado aqui -->
            </ul>
        </div>
    </div>

@endsection

@section('js')
    <script>
        $(document).ready(function() {
            function fetchProgresso() {
                $.ajax({
                    url: '{{ route("acompanhamento.progresso") }}',
                    method: 'GET',
                    success: function(data) {
                        var progressoList = $('#progresso-list');
                        progressoList.empty();
                        data.forEach(function(line) {
                            progressoList.append('<li>' + line + '</li>');
                        });
                    }
                });
            }

            // Chama fetchProgresso a cada 5 segundos
            setInterval(fetchProgresso, 5000);
            fetchProgresso(); // Chama uma vez imediatamente ao carregar a página
        });

        
    </script>
@endsection
