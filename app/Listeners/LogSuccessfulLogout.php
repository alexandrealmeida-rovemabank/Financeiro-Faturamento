<?php

    namespace App\Listeners;

    use Illuminate\Auth\Events\Logout;
    use Illuminate\Http\Request;

    class LogSuccessfulLogout
    {
         protected $request;

        public function __construct(Request $request)
        {
            $this->request = $request;
        }

        public function handle(Logout $event): void
        {
             // Verifica se existe um usuário associado ao evento (pode não haver em alguns casos)
            if ($event->user) {
                activity()
                   ->causedBy($event->user)
                   ->withProperties(['ip_address' => $this->request->ip()])
                   ->log('Usuário deslogado com sucesso');
            }
        }
    }
