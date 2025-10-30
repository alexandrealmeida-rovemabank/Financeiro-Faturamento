@if($contratos->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-sm table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Número</th>
                    <th>Modalidade</th>
                    <th>Início</th>
                    <th>Término</th>
                    <th>Valor</th>
                    <th>Taxa Adm.</th>
                    <th>Situação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contratos as $contrato)
                    <tr>
                        <td>{{$contrato->id}}</td>
                        <td>{{$contrato->numero}}</td>
                        <td>{{$contrato->modalidade->nome ?? 'N/A'}}</td>
                        <td>{{$contrato->data_inicio->format('d/m/Y')}}</td>
                        <td>{{$contrato->data_termino->format('d/m/Y')}}</td>
                        <td>R$ {{ number_format($contrato->valor, 2, ',', '.') }}</td>
                        <td>{{ number_format($contrato->taxa_administrativa, 2, ',', '.') }}%</td>
                        <td><span class="badge badge-success">{{$contrato->situacao->nome ?? 'Ativo'}}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p>Nenhum contrato ativo encontrado.</p>
@endif

