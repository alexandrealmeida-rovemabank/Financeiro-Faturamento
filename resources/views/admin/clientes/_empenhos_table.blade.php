@php
    // Coleta todos os empenhos de todos os contratos e ordena pela data
    $allEmpenhos = $contratos->flatMap->empenhos->sortByDesc('data_cadastro');
@endphp

@if($allEmpenhos->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-sm table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Número</th>
                    <th>Contrato</th>
                    <th>Grupo</th>
                    <th>Valor</th>
                    <th>Saldo</th>
                    <th>Situação</th>
                    <th>Data Criação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allEmpenhos as $empenho)
                    <tr>
                        <td>{{$empenho->id}}</td>
                        <td>{{$empenho->numero_empenho}}</td>
                        <td>{{$empenho->contrato->numero ?? 'N/A'}}</td>
                        <td>{{$empenho->grupo->nome ?? 'N/A'}}</td>
                        <td>R$ {{ number_format($empenho->valor, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($empenho->saldo, 2, ',', '.') }}</td>
                        <td>
                            @php
                                $situacaoClass = [
                                    'aprovado' => 'badge-success',
                                    'fechado' => 'badge-secondary',
                                    'aguardando_aprovacao' => 'badge-warning',
                                    'reprovado' => 'badge-danger',
                                ];
                            @endphp
                            <span class="badge {{ $situacaoClass[$empenho->situacao] ?? 'badge-light' }}">{{ str_replace('_', ' ', ucfirst($empenho->situacao)) }}</span>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($empenho->data_cadastro)->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p>Nenhum empenho encontrado para os contratos ativos.</p>
@endif

