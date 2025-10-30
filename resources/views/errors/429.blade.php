@extends('errors.layout')

@section('title', 'Muitas Requisições')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 text-center">
    <h1 class="text-7xl font-bold text-yellow-500">429</h1>
    <p class="mt-4 text-xl text-gray-700">Muitas Requisições</p>
    <p class="text-sm text-gray-500 mt-2">
        Você realizou muitas solicitações em um curto intervalo de tempo.<br>
        Por favor, aguarde alguns instantes antes de tentar novamente.
    </p>
    <a href="{{ url('/') }}" class="mt-6 inline-block px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
        Voltar ao Início
    </a>
</div>
@endsection
