<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // SE o usuário não estiver autenticado OU não for admin
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            abort(403, 'Acesso não autorizado'); // bloqueia com erro 403
        }

        return $next($request); // permite continuar
    }
}
