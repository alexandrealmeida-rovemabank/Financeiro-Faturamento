@extends('errors.layout')


@section('title', 'Não Autorizado')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 text-center">
    <h1 class="text-7xl font-bold text-orange-500">401</h1>
    <p class="mt-4 text-xl text-gray-700">Você precisa estar autenticado para acessar esta página.</p>
    <a href="{{ route('login') }}" class="mt-6 inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
        Fazer Login
    </a>
</div>
@endsection
