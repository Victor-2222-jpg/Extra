<?php

use App\Http\Controllers\Administrador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\controlcontroller;
use App\Http\Controllers\JuegoController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
    Route::post('send-activation-code', [AuthController::class, 'sendActivationCode']);
    
}); 

Route::get('control/{numero_cuenta}', [controlcontroller::class, 'index'])->name('activacion');
Route::post('juego', [JuegoController::class, 'iniciarJuego']);
Route::post('juego/historial',[JuegoController::class,'historialJuegos']);
Route::get('juego', [JuegoController::class, 'MostrarDisponibles']);
Route::get('juego/unirse/{juegoId}', [JuegoController::class, 'unirseAJuego']);
Route::get('juego/resultados', [JuegoController::class, 'consultarResultados']);
Route::post('juego/salir', [JuegoController::class, 'abandonarjuego']);
Route::post('juego/{letra}', [JuegoController::class, 'jugar'])
->where('letra', '^[a-zA-Z]$');
Route::get('admin/juegos', [Administrador::class, 'adminHistorialJuegos']);
Route::post('admin',[Administrador::class,'DesactivarCuenta']);
Route::get('admin',[Administrador::class,'VerTodaaslaspartidas']);





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





/*


<?php
public function iniciarJuego(Request $request)
{
    $user = auth()->user()->id;
    $palabra = $this->obtenerPalabraAleatoria();

    $juego = Juego::create([
        'user_id' => $user,
        'adivinadas' => json_encode([]),
        'status' => 'en progreso',
        'palabra' => $palabra,
        'intentos_restantes' => env('MAX_ATTEMPTS', 6) // Valor por defecto 6 si no existe en .env
    ]);

    $this->enviarMensajeWhatsApp('+5218713944040', "¡Juego iniciado! La palabra tiene " . strlen($palabra) . " letras.");

    return response()->json([
        'message' => 'Juego iniciado',
        'juego' => $juego,
        'palabra_oculta' => str_repeat('_', strlen($palabra)),
        'intentos_restantes' => $juego->intentos_restantes
    ], 201);
}

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

    if (strlen($letra) !== 1) {
        return response()->json([
            'message' => 'Debes ingresar una sola letra',
            'estado' => 'error'
        ], 400);
    }

    $adivinadas = json_decode($juego->adivinadas, true) ?? [];

    if (in_array($letra, $adivinadas)) {
        return response()->json([
            'message' => 'Ya intentaste esta letra',
            'palabra_actual' => $this->mostrarPalabra($juego->palabra, $adivinadas),
            'estado' => 'repetida'
        ], 400);
    }

    $adivinadas[] = $letra;
    $juego->adivinadas = json_encode($adivinadas);

    if (strpos($juego->palabra, $letra) === false) {
        $juego->intentos_restantes--;
    }

    $palabraActual = $this->mostrarPalabra($juego->palabra, $adivinadas);

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



