@extends('errors.layout')

@section('title', 'Serviço Indisponível')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 text-center">
    <h1 class="text-7xl font-bold text-purple-500">503</h1>
    <p class="mt-4 text-xl text-gray-700">Estamos em manutenção no momento.</p>
    <p class="text-sm text-gray-500 mt-2">Voltaremos em breve. Obrigado pela paciência!</p>
    <a href="{{ url('/') }}" class="mt-6 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
        Recarregar Página
    </a>
</div>
@endsection
