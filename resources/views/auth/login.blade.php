@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@section('adminlte_css')
    <style>
        body {
            background: linear-gradient(135deg, #004ae6, #00AEEF);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .auth-card {
            background: #fff;
            width: 100%;
            max-width: 900px;
            height: 500px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            display: flex;
            overflow: hidden;
            animation: fadeIn 0.8s ease-in-out;
        }

        .auth-slide {
            width: 70%;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .auth-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .auth-slide img.active {
            opacity: 1;
        }

        .slide-controls {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: row; /* agora fica vertical */
            align-items: center;
            gap: 10px;
            z-index: 2;
            background: rgba(255, 255, 255, 0);
            padding: 0.5rem;
            border-radius: 10px;
        }

        .indicators {
            display: flex;
            flex-direction: row; 
            gap: 10px;
        }

        .indicator {
            width: 12px;
            height: 12px;
            background: #ccc;
            border-radius: 50%;
            cursor: pointer;
            transition: background 0.3s;
        }

        .indicator.active {
            background: #004ae6;
        }

        .control-btn {
            background: #004ae6;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: background 0.3s;
        }

        .control-btn:hover {
            background: #003bb5;
        }

        .auth-form {
            width: 50%;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 1.5rem;
        }

        .auth-form .logo {
            display: flex;
            justify-content: center;
        }

        .auth-form .logo img {
            max-width: 100px;
        }

        .auth-form form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .auth-form input {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            width: 100%;
            transition: border-color 0.3s;
        }

        .auth-form input:focus {
            border-color: #004ae6;
            outline: none;
        }

        .auth-form button {
            background: #004ae6;
            color: #fff;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .auth-form button:hover {
            background: #003bb5;
        }

        .auth-form .footer-link {
            text-align: center;
            font-size: 0.9rem;
        }

        .auth-form .footer-link a {
            color: #004ae6;
            text-decoration: none;
            transition: color 0.3s;
        }

        .auth-form .footer-link a:hover {
            color: #002e99;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media(max-width: 768px) {
            .auth-card {
                flex-direction: column;
                height: auto;
            }
            .auth-slide, .auth-form {
                width: 100%;
                height: 250px;
            }
        }

        .login-box, .register-box {
            width: 50%;
        }

        .login-page .card {
            box-shadow: none !important;
            border: none !important;
            background: transparent !important;
        }
    </style>
@stop

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )
@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $register_url = $register_url ? route($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $register_url = $register_url ? url($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('auth_body')
    <div class="auth-card">
        <div class="auth-slide">
            <img src="{{ asset('vendor/adminlte/dist/img/slide1.png') }}" class="active" alt="Slide 1">
            <!-- <img src="{{ asset('vendor/adminlte/dist/img/slide2.png') }}" alt="Slide 2"> -->
            <img src="{{ asset('vendor/adminlte/dist/img/slide3.png') }}" alt="Slide 3">
            <img src="{{ asset('vendor/adminlte/dist/img/slide4.png') }}" alt="Slide 4">

            <div class="slide-controls">
                <!-- <button class="control-btn prev">Anterior</button> -->

                <div class="indicators">
                    <div class="indicator active" data-slide="0"></div>
                    <!-- <div class="indicator" data-slide="1"></div> -->
                    <div class="indicator" data-slide="2"></div>
                    <div class="indicator" data-slide="3"></div>
                    
                </div>

                <!-- <button class="control-btn next">Próximo</button> -->
            </div>
        </div>

        <div class="auth-form">
            <div class="logo">
                <img src="{{ asset('vendor/adminlte/dist/img/Rovema Pay.png') }}" alt="Logo">
            </div>

            <form action="{{ $login_url }}" method="post">
                @csrf

                <input type="email" name="email" class="@error('email') is-invalid @enderror"
                       value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}" autofocus>

                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

                <input type="password" name="password" class="@error('password') is-invalid @enderror"
                       placeholder="{{ __('adminlte::adminlte.password') }}">

                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

                <button type="submit">
                    <span class="fas fa-sign-in-alt"></span>
                    {{ __('adminlte::adminlte.sign_in') }}
                </button>
            </form>

            @if($password_reset_url)
                <div class="footer-link">
                    <a href="{{ $password_reset_url }}">
                        {{ __('adminlte::adminlte.i_forgot_my_password') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
@stop

@section('adminlte_js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slides = document.querySelectorAll('.auth-slide img');
            const indicators = document.querySelectorAll('.indicator');
            let current = 0;

            function showSlide(index) {
                slides.forEach((slide, i) => {
                    slide.classList.toggle('active', i === index);
                    indicators[i].classList.toggle('active', i === index);
                });
            }

            function nextSlide() {
                current = (current + 1) % slides.length;
                showSlide(current);
            }

            indicators.forEach(indicator => {
                indicator.addEventListener('click', () => {
                    current = parseInt(indicator.getAttribute('data-slide'));
                    showSlide(current);
                });
            });

            setInterval(nextSlide, 5000);
        });

       
    </script>
@stop

@section('auth_footer')
@stop
