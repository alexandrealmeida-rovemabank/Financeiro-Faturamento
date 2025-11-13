<hr>
<h6>Descontos Aplicados</h6>
<table class="table table-sm table-striped">
    <thead>
        <tr>
            <th>Tipo</th>
            <th>Valor</th>
            <th>Calculado (R$)</th>
            <th>Usuário</th>
            <th>Justificativa</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>
        @forelse($descontos as $desconto)
        <tr>
            <td>
                @if($desconto->tipo == 'fixo')
                <span class="badge badge-primary">Fixo</span>
                @else
                <span class="badge badge-info">Percentual</span>
                @endif
            </td>
            <td>
                {{ $desconto->tipo == 'fixo' ? 'R$ ' : '' }}
                {{ number_format($desconto->valor, 2, ',', '.') }}
                {{ $desconto->tipo == 'percentual' ? '%' : '' }}
            </td>
            <td>R$ {{ number_format($desconto->valor_calculado, 2, ',', '.') }}</td>
            <td>{{ $desconto->usuario->name ?? 'N/A' }}</td>
            <td>{{ $desconto->justificativa ?? '...' }}</td>
            <td>
                @if($fatura->status != 'recebida')
                <button type="button" class="btn btn-xs btn-danger btn-remover-desconto"
                    data-url="{{ route('faturamento.removerDesconto', $desconto) }}">
                    <i class="fa fa-trash"></i>
                </button>
                @else
                -
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="text-center">Nenhum desconto manual aplicado.</td>
        </tr>
        @endforelse
    </tbody>
</table>