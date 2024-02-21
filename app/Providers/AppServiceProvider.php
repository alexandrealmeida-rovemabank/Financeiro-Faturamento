<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Validator::extend('cnpj_unique', function ($attribute, $value, $parameters, $validator) {
            // Remover qualquer caractere não numérico do CNPJ
            $cnpj = preg_replace('/[^0-9]/', '', $value);

            // Verificar a unicidade do CNPJ após remover a pontuação
            return \App\Models\Credenciado::where('cnpj', $cnpj)->count() === 0;
        });
    }
}
