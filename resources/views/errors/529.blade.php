@extends('errors.layout')

@section('title', 'Serviço Indisponível')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 text-center">
    <h1 class="text-7xl font-bold text-purple-529">529</h1>
    <p class="mt-4 text-xl text-gray-700">Sistema Sobrecarregado.</p>
    <p class="text-sm text-gray-500 mt-2">Nosso servidor está recebendo muitas solicitações no momento.
Tente novamente em alguns instantes</p>
    <a href="{{ url('/') }}" class="mt-6 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
        Voltar ao Início
    </a>
</div>
@endsection
