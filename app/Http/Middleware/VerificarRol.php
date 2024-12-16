<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificarRol
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/auth/login')) {
            $email = $request->input('email');
            $user = \App\Models\User::where('email', $email)->first();
            
            if (!$user || $user->roles->rol === 'Invitado' || !$user->cuenta_activa) {
                return response()->json([
                    'error' => 'Debes activar tu cuenta antes de iniciar sesion'
                ], 403);
            }
            return $next($request);
        }

        $user = $request->user();

        if ($user) {
            $userRole = $user->roles->rol;

            if (in_array($userRole, ['Jugador', 'Administrador'])) {
                return $next($request);
            }
        }

        return response()->json([
            'error' => 'No tienes permiso para acceder a esta ruta.'
        ], 403);
    }
}