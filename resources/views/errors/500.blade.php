@extends('errors.layout')

@section('title', 'Erro Interno do Servidor')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 text-center">
    <h1 class="text-7xl font-bold text-blue-500">500</h1>
    <p class="mt-4 text-xl text-gray-700">Ocorreu um erro interno no servidor.</p>
    <p class="text-sm text-gray-500 mt-2">Tente novamente mais tarde. Se o problema persistir, contate o suporte.</p>
    <a href="{{ url('/') }}" class="mt-6 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
        Voltar ao Início
    </a>
</div>
@endsection
