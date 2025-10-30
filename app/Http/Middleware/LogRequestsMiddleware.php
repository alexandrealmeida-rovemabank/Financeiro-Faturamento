<?php

    namespace App\Http\Middleware;

    use Closure;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Symfony\Component\HttpFoundation\Response;

    class LogRequestsMiddleware
    {
        /**
         * Handle an incoming request.
         *
         * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
         */
        public function handle(Request $request, Closure $next): Response
        {
            // Executa a requisição primeiro para ter a resposta
            $response = $next($request);

            // Só loga se o usuário estiver autenticado e não for requisição AJAX (opcional)
            if (Auth::check() && !$request->ajax()) {
                $user = Auth::user();
                $routeName = $request->route() ? $request->route()->getName() : 'N/A'; // Nome da rota
                $url = $request->fullUrl(); // URL completa
                $method = $request->method(); // GET, POST, PUT, DELETE

                activity()
                    ->causedBy($user)
                    ->withProperties([
                        'ip_address' => $request->ip(),
                        'url' => $url,
                        'route_name' => $routeName,
                        'method' => $method,
                        // 'user_agent' => $request->userAgent() // Opcional: navegador do usuário
                        ])
                    ->log("Acessou a página: {$routeName} ({$method})");
            }

            return $response;
        }
    }
