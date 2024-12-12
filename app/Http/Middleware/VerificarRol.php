<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificarRol
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

        if ($user) {
            $userRole = $user->roles->rol;

           
            if ($userRole === 'administrador') {
                
                return $next($request);
            } elseif ($userRole === 'user') {
               
                if ($request->isMethod('get')) {
                    return $next($request);
                }
            }
        }

        return response()->json(['error' => 'No tienes permiso para acceder a esta ruta.'], 403);
    }
}
