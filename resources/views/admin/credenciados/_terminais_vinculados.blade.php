<div class="table-responsive">
    <table class="table table-bordered table-hover table-sm">
        <thead class="thead-light">
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Serial</th>
                <th>Modelo</th>
                <th>Versão</th>
                <th>Fornecedor</th>
                <th>Garantia</th>
                <th>Status</th>
                <th>Credenciado Alocado</th>
                <th>CNPJ</th>
                <th>Data Cadastro</th>
                <th>Cadastrado por</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($terminais as $pos)
                <tr>
                    <td>{{ $pos->id }}</td>
                    <td>{{ $pos->codigo ?? '—' }}</td>
                    <td>{{ $pos->serial ?? '—' }}</td>
                    <td>{{ $pos->modelo ?? '—' }}</td>
                    <td>{{ $pos->versao ?? '—' }}</td>
                    <td>{{ $pos->fornecedor ?? '—' }}</td>
                    <td>{{ $pos->garantia ?? '—' }}</td>
                    <td>
                        {!! $pos->ativo
                            ? '<span class="badge badge-success">Ativo</span>'
                            : '<span class="badge badge-danger">Inativo</span>' !!}
                    </td>
                    <td>{{ $pos->credenciado->nome ?? '—' }}</td>
                    <td>{{ $pos->credenciado->cnpj ?? '—' }}</td>
                    <td>{{ $pos->data_cadastro ? \Carbon\Carbon::parse($pos->data_cadastro)->format('d/m/Y') : '—' }}</td>
                    <td>{{ $pos->usuarioCadastro->nome ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center text-muted">
                        Nenhum terminal vinculado encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
