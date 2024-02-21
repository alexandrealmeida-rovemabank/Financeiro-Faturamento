<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title> Credenciados </title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="ugEVOgHfjTMgQA3JnIXIZPnrPxKJQfZcuZsRjEkR">
    <link rel="stylesheet" href="{{ public_path('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ public_path('vendor/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ public_path('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ public_path('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic') }}">
    <link rel="stylesheet" href="{{ public_path('//cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ public_path('//cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ public_path('https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ public_path('//cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css') }}">
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            margin-bottom: 20px;
        }

        .header-image,
        .footer-image {
            max-width: 100%;
        }

        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #f8f9fa; /* Cor de fundo do rodapé */
            padding: 20px 0;
            text-align: center;
        }

        .footer-image {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="cont">
    <!-- Cabeçalho -->
    <header>
        <img src="{{ asset('vendor/adminlte/dist/img/header_pdf_uzz.png') }}" class="header-image" alt="Header Image">
    </header>
    <div class="container">
    <!-- Parte específica da página -->
    <div class="card">
        <div class="card-header">
          <h1 class="m-0 card-title text-dark">Informações</h1>
        </div>
        <div class="card-body">

            <div class="col-sm-6">
                <!-- radio -->
                <div class="form-group" style="display: block">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" enabled="true" name="status" value="Ativo" @disabled(true)  @if ($credenciado->status=='Ativo') checked @endif>
                    <label class="form-check-label">Ativo</label>
                  </div>
                  <div class="form-check">

                    <input style="" class="form-check-input" type="radio" enabled="true" name="status"@disabled(true) value="Inativo"  @if ($credenciado->status=='Inativo') checked @endif>
                    <label class="form-check-label">Inativo</label>
                  </div>
                 </div>
            </div>
            <div class="row">
              <div class="col-sm-6">
                <!-- text input -->
                <div class="form-group">
                  <label>CNPJ: </label>
                  <a  name="cnpj" pattern="\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}"> {{ $credenciado->cnpj_formatted }} </a>

                </div>
              </div>
            </div>
            <div class="row">
                <div class="col-sm-6">

                    <div class="form-group">
                        <label>Nome Fantasia: </label>
                        <a name="nome_fantasia">{{ $credenciado->nome_fantasia }}</a>
                    </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>Razão Social: </label>
                    <a>{{ $credenciado->razao_social }}</a>
                  </div>
                </div>
            </div>
            {{-- Endereço --}}
            <div class="row">
                <div class="col-sm-6">
                  <!-- text input -->
                  <div class="form-group">
                    <label>CEP: </label>
                    <a>{{ $credenciado->cep }}</a>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>Endereço: </label>
                    <a>{{ $credenciado->endereco }}</a>
                  </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                  <!-- text input -->
                  <div class="form-group">
                    <label>Número: </label>
                    <a>{{ $credenciado->numero }}</a>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>Bairro: </label>
                    <a>{{ $credenciado->bairro }}</a>
                  </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                  <!-- text input -->
                  <div class="form-group">
                    <label>Cidade: </label>
                    <a>{{ $credenciado->cidade }}</a>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>Estado: </label>
                    <a>{{ $credenciado->estado}}</a>
                  </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6">
                  <!-- text input -->
                  <div class="form-group">
                    <label>Telefone: </label>
                    <a >{{ $credenciado->telefone }}</a>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="form-group">
                    <label>Celular: </label>
                    <a >{{ $credenciado->celular }}</a>
                  </div>
                </div>
            </div>
            {{-- selecionar produto --}}
            <div class="row">
                <div class="col-sm-6">
                    <!-- Select multiple-->
                    <div class="form-group">
                        <label>Produtos: </label>
                        <?php
                            $produtosSelecionados = json_decode($credenciado->produto, true) ?? [];
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" name="produto[]" @disabled(true) type="checkbox" value="BIONIO" {{ in_array('BIONIO', $produtosSelecionados) ? 'checked' : '' }}>
                            <label class="form-check-label">BIONIO</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" name="produto[] " @disabled(true) type="checkbox" value="ELIQ" {{ in_array('ELIQ', $produtosSelecionados) ? 'checked' : '' }}>
                            <label class="form-check-label">ELIQ</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" name="produto[]" @disabled(true) type="checkbox" value="MAQUININHA" {{ in_array('MAQUININHA', $produtosSelecionados) ? 'checked' : '' }}>
                            <label class="form-check-label">MAQUININHA</label>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="card">
        <div class="card-header">
          <h1 class="m-0 card-title text-dark">Terminais Vinculados</h1>
        </div>

        <div class="card-body">
            <table id="credenciados" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Terminal</th>
                        <th>Marca</th>
                        <th>Modedlo</th>
                        <th>Chip</th>
                        <th>Produto</th>
                        <th>Data de Vinculação</th>
                    </tr>
                </thead>
                 <tbody>
                     @foreach($terminal as $terminais)
                     @if ( $terminais->id_credenciado == $credenciado->id && $terminais->status =='Vinculado')
                    <tr>
                        <td>{{ $terminais->id}}</td>
                        <td>{{ $terminais->estoque->numero_serie}}</td>
                        <td>{{ $terminais->estoque->fabricante }}</td>
                        <td>{{ $terminais->estoque->modelo }}</td>
                        <td>{{ $terminais->chip }}</td>
                        <td>{{ $terminais->produto }}</td>
                        <td>{{ $terminais->created_at}}</td>
                    </tr>
                    @endif
                   @endforeach
                </tbody>

            </table>
        </div>


    </div>
    </div>
    </div>

    <!-- Rodapé -->
    <footer>
        <img src="{{ asset('vendor/adminlte/dist/img/footer_pdf_uzz.png') }}" class="footer-image" alt="Footer Image">
    </footer>

    <script src="http://localhost:8000/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost:8000/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="http://localhost:8000/vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <script src="http://localhost:8000/vendor/adminlte/dist/js/adminlte.min.js"></script>
    <script src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="//cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="//cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="//cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="//cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="http://localhost:8000/vendor/adminlte/dist/css/custom.css">
    </body>
    </html>
