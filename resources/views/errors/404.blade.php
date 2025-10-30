@extends('errors.layout')

@section('title', 'Página não encontrada')

@section('content')
    <div class="error-code">404</div>
    <div class="error-message">Página não encontrada</div>
    <div class="error-details">A página que você tentou acessar não existe ou foi removida.</div>
    <a href="{{ url('/') }}" class="btn"><i class="fas fa-home"></i> Voltar ao início</a>
@endsection
