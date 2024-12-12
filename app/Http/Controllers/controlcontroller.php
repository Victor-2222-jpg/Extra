<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class controlcontroller extends Controller
{
    public function index(int $numero_cuenta)
    {
        $user = User::where('numero_verificacion', $numero_cuenta)->first();
        $user->cuenta_activa = true;
        $user->role_id=2;
        $user->save();
        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }
        return response()->json(['message' => 'Cuenta activada'], 200);	
    }
    
}


/*


<?php
public function jugar(string $letra) 
{
    $user = auth()->user()->id;
    $juego = Juego::where('user_id', $user)
        ->where('status', 'en progreso')
        ->first();

    if (!$juego) {
        return response()->json([
            'message' => 'No tienes un juego en progreso.',
        ], 404);
    }

    $letra = strtolower($letra);
    
    // Validar que sea una sola letra
    if (strlen($letra) !== 1) {
        return response()->json([
            'message' => 'Debes ingresar una sola letra',
            'estado' => 'error'
        ], 400);
    }

    // Obtener las letras ya adivinadas
    $adivinadas = json_decode($juego->adivinadas, true) ?? [];

    // Verificar si la letra ya fue intentada
    if (in_array($letra, $adivinadas)) {
        return response()->json([
            'message' => 'Ya intentaste esta letra',
            'palabra_actual' => $this->mostrarPalabra($juego->palabra, $adivinadas),
            'estado' => 'repetida'
        ], 400);
    }

    // Agregar la letra a las adivinadas
    $adivinadas[] = $letra;
    $juego->adivinadas = json_encode($adivinadas);

    // Verificar si la letra está en la palabra
    if (strpos($juego->palabra, $letra) === false) {
        $juego->intentos_restantes--;
    }

    // Obtener palabra actual con las letras adivinadas
    $palabraActual = $this->mostrarPalabra($juego->palabra, $adivinadas);

    // Verificar victoria o derrota
    if ($palabraActual === $juego->palabra) {
        $juego->status = 'ganado';
        $mensaje = "¡Felicidades! Has ganado. La palabra era: {$juego->palabra}";
    } elseif ($juego->intentos_restantes <= 0) {
        $juego->status = 'perdido';
        $mensaje = "Game Over. La palabra era: {$juego->palabra}";
    } else {
        $mensaje = "Te quedan {$juego->intentos_restantes} intentos";
    }

    $juego->save();
    
    $this->enviarMensajeWhatsApp('+5218713944040', $mensaje);

    return response()->json([
        'message' => $mensaje,
        'palabra_actual' => $palabraActual,
        'letras_adivinadas' => $adivinadas,
        'intentos_restantes' => $juego->intentos_restantes,
        'estado' => $juego->status
    ]);
}






*/