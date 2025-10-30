@if($unidades->isEmpty())
    <div class="p-4 text-center text-muted bg-light rounded shadow-sm">
        <i class="fas fa-info-circle me-2 text-primary"></i>
        Este credenciado não possui unidades cadastradas.
    </div>
@else
    <div class="card border-0 shadow-sm mt-2 mb-2">
        <div class="card-header bg-light d-flex align-items-center">
            <i class="fas fa-building me-2 text-primary"></i>
            <h5 class="mb-0">Unidades Vinculadas</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-bordered mb-0 unidades-table align-middle">
                <thead class="table-light">
                    <tr class="text-secondary">
                        <th style="width: 5%">ID</th>
                        <th style="width: 30%">Nome da Unidade</th>
                        <th style="width: 20%">CNPJ</th>
                        <th style="width: 25%">Cidade / UF</th>
                        <th style="width: 15%" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unidades as $unidade)
                        <tr>
                            <td class="fw-semibold text-secondary">{{ $unidade->id }}</td>
                            <td>{{ $unidade->nome }}</td>
                            <td><span class="text-monospace">{{ $unidade->cnpj }}</span></td>
                            <td>{{ $unidade->municipio->nome ?? 'N/A' }} / {{ $unidade->municipio->estado->sigla ?? 'N/A' }}</td>
                            <td class="text-center">
                                @if($unidade->ativo)
                                    <span class="badge bg-success px-3 py-2 shadow-sm">
                                        <i class="fas fa-check-circle me-1"></i> Ativo
                                    </span>
                                @else
                                    <span class="badge bg-danger px-3 py-2 shadow-sm">
                                        <i class="fas fa-times-circle me-1"></i> Inativo
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
