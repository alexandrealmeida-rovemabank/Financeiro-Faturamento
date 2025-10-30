@extends('adminlte::page')

@section('title', 'Tecnologia Uzzipay')


@section('content_header')

    <!-- <h1 class="m-0 text-dark"><i>Bem-Vindo, {{ $user->name }}</i></h1>
    <br> -->

@stop

@section('content')
    @include('layouts.notificacoes')


    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Dashboard</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        Você está logado!
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection