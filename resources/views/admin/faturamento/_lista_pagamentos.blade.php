@php
    // Helper para formatar o nome do arquivo
    function getCleanFilename($path) {
        if (empty($path)) return 'N/A';
        return basename($path);
    }
@endphp

<hr>
<h6>Pagamentos Registrados</h6>
<table class="table table-sm table-striped">
    <thead>
        <tr>
            <th>Data</th>
            <th>Valor Pago (R$)</th>
            <th>Usuário</th>
            <th>Comprovante</th>
            <th>Ação</th>
        </tr>
    </thead>
    <tbody>
        @forelse($pagamentos as $pagamento)
        <tr>
            <td>{{ $pagamento->data_pagamento->format('d/m/Y') }}</td>
            <td>R$ {{ number_format($pagamento->valor_pago, 2, ',', '.') }}</td>
            <td>{{ $pagamento->usuario->name ?? 'N/A' }}</td>
            <td>
                @if($pagamento->comprovante_path)
                    {{-- Este é o link de download --}}
                    <a href="{{ Storage::url($pagamento->comprovante_path) }}" 
                       target="_blank" 
                       class="btn btn-xs btn-outline-info"
                       title="{{ getCleanFilename($pagamento->comprovante_path) }}">
                       <i class="fa fa-download"></i> Baixar
                    </a>
                @else
                    <span class="text-muted">N/A</span>
                @endif
            </td>
            <td>
                @if($fatura->status != 'recebida')
                <button type
                    ="button" class="btn btn-xs btn-danger btn-remover-pagamento"
                    data-url="{{ route('faturamento.removerPagamento', $pagamento) }}">
                    <i class="fa fa-trash"></i>
                </button>
                @else
                -
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">Nenhum pagamento registrado.</td>
        </tr>
        @endforelse
    </tbody>
</table>