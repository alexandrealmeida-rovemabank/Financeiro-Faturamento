<div class="row">
    <div class="col-md-6">
        <p><strong>ID:</strong> {{ $empresa->id }}</p>
        <p><strong>Nome Fantasia:</strong> {{ $empresa->nome }}</p>
        <p><strong>Razão Social:</strong> {{ $empresa->razao_social }}</p>
        <p><strong>CNPJ:</strong> {{ $empresa->cnpj ?? 'N/A' }}</p>
        <p><strong>Email:</strong> {{ $empresa->email }}</p>
    </div>
    <div class="col-md-6">
        <p><strong>Status:</strong> {!! $empresa->ativo ? '<span class="badge badge-success">Ativo</span>' : '<span class="badge badge-danger">Inativo</span>' !!}</p>
        <p><strong>Endereço:</strong> {{ $empresa->logradouro }}, {{ $empresa->numero }} - {{ $empresa->bairro }}</p>
        <p><strong>Município:</strong> {{ $empresa->municipio->nome ?? 'N/A' }} / {{ $empresa->municipio->estado->sigla ?? 'N/A' }}</p>
        <p><strong>CEP:</strong> {{ $empresa->cep }}</p>
        <p><strong>Organização:</strong> {{ $empresa->organizacao->nome ?? 'N/A' }}</p>
        <p><strong>Data de Cadastro:</strong> {{ \Carbon\Carbon::parse($empresa->data_cadastro)->format('d/m/Y H:i') }}</p>
    </div>
</div>

