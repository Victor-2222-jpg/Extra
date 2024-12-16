<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $user = $request->user();

        if (!$user || $user->roles->rol !== 'Administrador') {
            return response()->json([
                'error' => 'Acceso denegado. Solo administradores pueden acceder a esta ruta.'
            ], 403);
        }
        return $next($request);
    }
}
