<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;

class LogFailedLogin
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        // Captura o email/tentativa de login
        $email = $event->credentials['email'] ?? 'desconhecido';

        activity()
            ->withProperties([
                'email_tentado' => $email,
                'ip_address' => $this->request->ip(),
            ])
            ->log('Tentativa de login falhou');
    }
}
