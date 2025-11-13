<div style="padding-left: 30px; background-color: #f8f9fa;">
    <table class="table table-sm table-borderless">
        <thead class="thead-light">
            <tr>
                <th>Subgrupo (Veículo)</th>
                <th class="text-right">Valor Bruto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($subgrupos as $item)
            <tr>
                <td><i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-2"></i> {{ $item->nome }}</td>
                <td class="text-right">R$ {{ number_format($item->valor_bruto, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="text-center text-muted">Nenhum subgrupo encontrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>