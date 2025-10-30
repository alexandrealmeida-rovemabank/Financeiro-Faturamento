@extends('errors.layout')

@section('title', 'Acesso Negado')

@section('content')
<div class="container text-center py-5">
    <div class="card shadow-lg border-0 rounded-4 mx-auto" style="max-width: 600px;">
        <div class="card-body p-5">
            <i class="fas fa-lock text-danger mb-3" style="font-size: 4rem;"></i>
            <h2 class="text-danger mb-3">Acesso Negado</h2>
            <p class="lead mb-4">Você não tem permissão para acessar esta página.</p>
            <p class="text-muted">Se acredita que isso é um engano, contate o administrador do sistema.</p>
            <a href="{{ url()->previous() }}" class="btn btn-outline-primary mt-4">
                <i class="fas fa-arrow-left me-2"></i> Voltar
            </a>
        </div>
    </div>
</div>
@endsection
