@php
    // Busca o código dealer (a relação já foi carregada no controller)
    $codDealer = $empresa->codigoDealer?->cod_dealer ?? '';
@endphp

<div class="row">
    <div class="col-md-6">
        <p><strong>ID:</strong> {{ $empresa->id }}</p>
        <p><strong>Nome Fantasia:</strong> {{ $empresa->nome }}</p>
        <p><strong>Razão Social:</strong> {{ $empresa->razao_social }}</p>
        <p><strong>CNPJ:</strong> {{ $empresa->cnpj ?? 'N/A' }}</p>
        <p><strong>Email:</strong> {{ $empresa->email }}</p>
        <p><strong>Endereço:</strong> {{ $empresa->logradouro }}, {{ $empresa->numero }} - {{ $empresa->bairro }}</p>
    </div>

    <div class="col-md-6">
        <p><strong>Status:</strong> {!! $empresa->ativo ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-danger">Inativo</span>' !!}</p>
        <p><strong>Município:</strong> {{ $empresa->municipio->nome ?? 'N/A' }} / {{ $empresa->municipio->estado->sigla ?? 'N/A' }}</p>
        <p><strong>CEP:</strong> {{ $empresa->cep }}</p>
        <p><strong>Organização:</strong> {{ $empresa->organizacao->nome ?? 'N/A' }}</p>
        <p><strong>Data de Cadastro:</strong> {{ \Carbon\Carbon::parse($empresa->data_cadastro)->format('d/m/Y H:i') }}</p>

        <hr>

        <form action="{{ route('clientes.updateCodigoDealer', $empresa->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="cod_dealer_{{ $empresa->id }}">Código Dealer</label>
                <div class="input-group">
                    <input type="text" 
                           id="cod_dealer_{{ $empresa->id }}" 
                           name="cod_dealer" 
                           class="form-control @error('cod_dealer') is-invalid @enderror" 
                           value="{{ old('cod_dealer', $codDealer) }}"
                           {{ !$empresa->cnpj ? 'disabled' : '' }}>
                    
                    @can('edit cliente')
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary" {{ !$empresa->cnpj ? 'disabled' : '' }}>Salvar</button>
                    </div>
                    @endcan
                </div>

                @if(!$empresa->cnpj)
                    <small class="form-text text-muted">É necessário um CNPJ para salvar um Código Dealer.</small>
                @endif
                @error('cod_dealer')
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        </form>
        </div>
</div>