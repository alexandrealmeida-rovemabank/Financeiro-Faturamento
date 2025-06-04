<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Auth\Notifications\Auth\ResetPassword;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];


    public function boot(): void
    {
    //     $this->registerPolicies();

    //     ResetPassword::toMailUsing(function($notifiable, $url){

    //     $exipra = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
    //     return (new MailMessage)
    //     ->subject('Reset de senha - Tecnologia Uzzipay')
    //     ->line('Você está recebendo este e -mail porque recebemos uma solicitação de redefinição de senha para sua conta.')
    //     ->action('Redefir Senha', $url)
    //     ->line('Este link de redefinição de senha expirará em:'. $exipra . 'minutos.')
    //     ->line('Se você não solicitou uma redefinição de senha, desconsidere este email.');


    // });

        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });
    }
}
