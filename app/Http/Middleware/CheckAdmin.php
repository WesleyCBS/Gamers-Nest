<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Verifica se o usuário está logado
        // 2. Verifica se o campo 'role' no banco de dados é igual a 'admin'
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request); // É o admin? Pode passar!
        }

        // Se não for admin, redireciona para a home com uma mensagem de errobootstrap/app.php
        return redirect('/')->with('error', 'Acesso restrito ao administrador.');
    }
}
