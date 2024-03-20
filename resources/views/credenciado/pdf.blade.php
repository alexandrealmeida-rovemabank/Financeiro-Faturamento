<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">

    <title> Credenciados </title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/vendor/adminlte/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="/vendor/adminlte/dist/css/custom.css">
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"
    integrity="sha512-qZvrmS2ekKPF2mSznTQsxqPgnpkI4DNTlrdUmTzrDgektczlKNRRhy5X5AAOnx5S09ydFYWWNSfcEqDTTHgtNA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

</head>

<body>
    <div class="body">
    <div class="cont">
    <!-- Cabeçalho -->
    <header>
        <img src="{{ asset('/vendor/adminlte/dist/img/header_pdf_uzz.png') }}" class="header-image" alt="Header Image">
    </header>
    <div class="container">
    <!-- Parte específica da página -->
    <div class="card">
        <div class="card-header">
          <h1 class="m-0 card-title text-dark">Informações</h1>

          <button onclick="downloadPDF()" >Download</button>
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
        <img src="{{ asset('/vendor/adminlte/dist/img/footer_pdf_uzz.png') }}" class="footer-image" alt="Footer Image">
    </footer>
</div>
       <script>
        function downloadPDF() {

            var doc = new jspdf.jsPDF('p', 'pc', 'letter');
            var margin = 1;
            var scale = 0.001 ;
            var scale_mobile = (doc.internal.pageSize.width - margin * 2) / document.body.getBoundingClientRect();
            var contentweb = document.querySelector('.body');

            if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)){

                doc.html(contentweb, {
                    x: margin,
                    y: margin,
                    html2canvas: {
                        scale: scale_mobile,
                    },
                    callback: function(doc){
                        doc.output('dataurlnewwindow', 'fichero-pdf.pdf');

                    }
                });
                }else{

                doc.html(contentweb, {
                    x: margin,
                    y: margin,
                    html2canvas: {
                        scale: scale,
                    },
                    callback: function(doc){
                        doc.output('dataurlnewwindow', 'fichero-pdf.pdf');

                    },
                    width: '1000',
                    windowWidth: '1000'
                });
                }


                }



       </script>
   </body>
   </html>
