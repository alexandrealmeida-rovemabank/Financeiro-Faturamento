@extends('errors.layout')

@section('title', 'Sessão Expirada')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 text-center">
    <h1 class="text-7xl font-bold text-green-500">419</h1>
    <p class="mt-4 text-xl text-gray-700">Sua sessão expirou por inatividade.</p>
    <p class="text-sm text-gray-500 mt-2">Recarregue a página ou faça login novamente.</p>
    <a href="{{ url()->previous() }}" class="mt-6 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
        Recarregar Página
    </a>
</div>
@endsection
