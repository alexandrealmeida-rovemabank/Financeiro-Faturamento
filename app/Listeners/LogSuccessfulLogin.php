<?php

    namespace App\Listeners;

    use Illuminate\Auth\Events\Login;
    use Illuminate\Http\Request;

    class LogSuccessfulLogin
    {
        protected $request;

        public function __construct(Request $request)
        {
            $this->request = $request;
        }

        public function handle(Login $event): void
        {
            activity()
               ->causedBy($event->user) // Quem causou
               ->withProperties(['ip_address' => $this->request->ip()]) // Propriedades extras
               ->log('Usuário logado com sucesso'); // Descrição
        }
    }
    
?>