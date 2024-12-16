<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Juego;
use App\Services\TwilioService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Faker\Factory as Faker;
use Database\Factories\SpanishWordProvider;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Http;
use App\Jobs\EnviarResumenSlack;
use App\Jobs\EnviarResumenSlackJob;
use App\Models\JuegoHistorial;

class JuegoController extends Controller
{
    protected $twilio;

   
    public function __construct(TwilioService $twilio)
    {
        $this->twilio = $twilio;
    }

    public function iniciarJuego(Request $request)
    {
        $user = auth()->user()->id;
        
        $palabra = $this->obtenerPalabraAleatoria();

        $juego = Juego::create([
            'user_id' => $user,
            'adivinadas' => json_encode([]),
            'status' => 'Iniciada',
            'palabra' => $palabra,
            'intentos_restantes' => env('MAX_ATTEMPTS', 5)
        ]);

        $this->enviarMensajeWhatsApp('+5218713944040', "¡Juego iniciado! La palabra tiene " . strlen($palabra) . " letras.");
        return response()->json([
            'message' => 'Juego iniciado',
            'juego_id' => $juego->id ,
            'palabra_oculta' => str_repeat('_', strlen($palabra))
        ], 201);
    }

    /*public function iniciarJuego(Request $request)
    {
        $user = auth()->user()->id;

        $juegoActivo = Juego::where('user_id', $user)
        ->where('status', 'en progreso')
        ->first();
    
    if ($juegoActivo) {
        return response()->json([
            'message' => 'Ya tienes un juego en progreso. Debes terminarlo antes de iniciar uno nuevo.',
            'juego_activo' => $juegoActivo
        ], 400);
    }


    
    $palabra = $this->obtenerPalabraAleatoria();
        
        $palabra = $this->obtenerPalabraAleatoria();

        

        $juego = Juego::create([
            'user_id' => $user,
            'adivinadas' => json_encode([]),
            'status' => 'en progreso',
            'palabra' => $palabra,
            'intentos_restantes' => env('MAX_ATTEMPTS', 5)
            
        ]);

      
        $this->enviarMensajeWhatsApp('+5218713944040', "¡Juego iniciado! La palabra tiene " . strlen($palabra) . " letras.");

        return response()->json([
            'message' => 'Juego iniciado',
            'juego' => $juego,
            'palabra' => $palabra,
            'palabra_oculta' => str_repeat('_', strlen($palabra))
        ], 201);
    } */


    public function unirseAJuego(Request $request, $juegoId)
{
    $user = auth()->user()->id;
    $juegoActivo = Juego::where('user_id', $user)
        ->where('status', 'en progreso')
        ->first();
    
    if ($juegoActivo) {
        return response()->json([
            'message' => 'Ya tienes un juego en progreso. Debes terminarlo antes de unirte a otro.',
            'juego_activo' => $juegoActivo
        ], 400);
    }
    
    $juego = Juego::where('id', $juegoId)
        ->where('status', 'Iniciada')
        ->first();
    
    if (!$juego) {
        return response()->json([
            'message' => 'El juego no está disponible o no existe.'
        ], 404);
    }
    $juego->user_id = $user;
    $juego->status = 'en progreso';
    $juego->save();

    $this->enviarMensajeWhatsApp(
        '+5218713944040', 
        "Te has unido al juego #{$juego->id}. La palabra tiene " . strlen($juego->palabra) . " letras."
    );
    
    return response()->json([
        'message' => 'Te has unido al juego exitosamente',
        'juego' => $juego,
        'palabra_oculta' => str_repeat('_', strlen($juego->palabra))
    ]);
}

public function jugar(string $letra) 
{
    $user = auth()->user()->id;
    $juego = Juego::where('user_id', $user)
        ->where('status', 'en progreso')
        ->orderBy('id', 'desc') 
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
        $mensaje = 'Ya intentaste esta letra';
        return response()->json([
            'message' => 'Ya intentaste esta letra',
            'palabra_actual' => $this->mostrarPalabra($juego->palabra, $adivinadas),
            'estado' => 'repetida'
        ], 400);
    }

    $adivinadas[] = $letra;
    $juego->adivinadas = json_encode($adivinadas);
    
    $acierto = strpos($juego->palabra, $letra) !== false;
    if (!$acierto) {
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

    
    JuegoHistorial::create([
        'juego_id' => $juego->id,
        'user_id' => $user,
        'letra' => $letra,
        'palabra_actual' => $palabraActual,
        'intentos_restantes' => $juego->intentos_restantes,
        'acierto' => $acierto,
        'estado_juego' => $juego->status
    ]);

    $juego->save();

   
    if ($juego->status === 'ganado' || $juego->status === 'perdido') {
        
        EnviarResumenSlack::dispatch($juego->id)->delay(now()->addMinute());
    }
    
    $this->enviarMensajeWhatsApp('+5218713944040', $mensaje);

    return response()->json([
        'message' => $mensaje,
        'palabra_actual' => $palabraActual,
        'letras_intentadas' => $adivinadas,
        'intentos_restantes' => $juego->intentos_restantes,
        'estado' => $juego->status
    ]);
}
    
    public function mostrarPalabra($palabra, $adivinadas)
    {
        $palabraOculta = '';
        foreach (str_split($palabra) as $letra) {
            if (in_array($letra, $adivinadas)) {
                $palabraOculta .= $letra;
            } else {
                $palabraOculta .= '_';
            }
        }
        return $palabraOculta;
    }

    private function enviarMensajeWhatsApp($to, $message)
    {
        try {
            $this->twilio->sendWhatsAppMessage($to, $message);
        } catch (\Exception $e) {
            
            Log::error("Error enviando mensaje de WhatsApp: " . $e->getMessage());
        }
    }


    public function abandonarjuego()
    {
        $user = auth()->user()->id;

        $juego = Juego::where('user_id', $user)
            ->where('status', 'en progreso')
            ->orderBy('id', 'desc')
            ->first();

        if (!$juego) {
            return response()->json([
                'message' => 'No tienes un juego en progreso.',
            ], 404);
        }

        $juego->status = 'perdido';
        $juego->save();

        $this->enviarMensajeWhatsApp('+5218713944040', "Has abandonado el juego. La palabra era: {$juego->palabra}");

        return response()->json([
            'message' => 'Juego abandonado',
        ]);
    }


    public function MostrarDisponibles()
{
    $juegos = Juego::where('status', 'Iniciada')
        ->orderBy('id', 'desc')
        ->get();
        $juegosdatos = [];
        foreach ($juegos as $juego) {
            $juegosdatos[] = [
                'id' => $juego->id,
                'usuario' => $juego->user->id,
                'palabra' => str_repeat('*', strlen($juego->palabra)),
                'intentos_restantes' => $juego->intentos_restantes,
                'fecha_inicio' => $juego->created_at->format('Y-m-d H:i:s')
            ];
        }

    return response()->json([
        'message' => 'Juegos disponibles',
        'juegos' => $juegosdatos
    ]);
}





public function consultarResultados(Request $request)
{
    $user = auth()->user()->id;
    
    $query = Juego::where('user_id', $user);
    
    
    $juegos = $query->orderBy('created_at', 'desc')->get();
    
    $estadisticas = [
        'total_juegos' => $juegos->count(),
        'juegos_ganados' => $juegos->where('status', 'ganado')->count(),
        'juegos_perdidos' => $juegos->where('status', 'perdido')->count(),
        'juegos_abandonados' => $juegos->where('status', 'abandonado')->count()
    ];
    
    $historial = [];
    foreach ($juegos as $juego) {
        $historial[] = [
            'id' => $juego->id,
            'palabra' => $juego->status !== 'en progreso' ? $juego->palabra : str_repeat('*', strlen($juego->palabra)),
            'estado' => $juego->status,
            'intentos_totales' => env('MAX_ATTEMPTS', 5),
            'intentos_usados' => env('MAX_ATTEMPTS', 5) - $juego->intentos_restantes,
            'letras_adivinadas' => json_decode($juego->adivinadas, true),
            'fecha_inicio' => $juego->created_at->format('Y-m-d H:i:s'),
            'fecha_finalización' => $juego->updated_at->format('Y-m-d H:i:s'),
            'duración' => $juego->created_at->diffInMinutes($juego->updated_at) . ' minutos',
            'resultado_final' => $juego->status !== 'en progreso' 
                ? $this->mostrarPalabra($juego->palabra, json_decode($juego->adivinadas, true))
                : str_repeat('_', strlen($juego->palabra))
        ];
    }

    return response()->json([
        'estadisticas' => $estadisticas,
        'historial' => $historial
    ]);
}



    private function obtenerPalabraAleatoria()
{
    try {
        $response = Http::get('https://random-word-api.herokuapp.com/word', [
            'lang' => 'es',
            'number' => 1
        ]);

        if ($response->successful()) {
            return strtolower($response->json()[0]);
        }

       
        throw new \Exception('Error obteniendo palabra de la API');

    } catch (\Exception $e) {
        Log::error('Error obteniendo palabra aleatoria: ' . $e->getMessage());
        
        return $this->generarPalabraAlternativa();
    }
}

private function generarPalabraAlternativa()
{
    $consonantes = ['b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','y','z'];
    $vocales = ['a','e','i','o','u'];
    
    $palabra = '';
    $longitud = rand(4, 8);
    
    for($i = 0; $i < $longitud; $i++) {
        $palabra .= ($i % 2 == 0) 
            ? $consonantes[array_rand($consonantes)]
            : $vocales[array_rand($vocales)];
    }
    
    return $palabra;
}
//historial para juegos por usuario
public function historialJuegos(Request $request)
{
    $user = auth()->user()->id;
    $query = Juego::where('user_id', $user);
    
    $juegos_raw = $query->orderBy('created_at', 'desc')->get();
    
    $historial = [];
    $juegos_ganados = 0;
    $juegos_perdidos = 0;
    
    foreach ($juegos_raw as $juego) {
        if ($juego->status === 'ganado') {
            $juegos_ganados++;
        } elseif ($juego->status === 'perdido') {
            $juegos_perdidos++;
        }
        
        // Construir array de detalles del juego
        $historial[] = [
            'id' => $juego->id,
            'palabra' => $juego->palabra,
            'estado' => $juego->status,
            'intentos_realizados' => env('MAX_ATTEMPTS', 5) - $juego->intentos_restantes,
            'intentos_restantes' => $juego->intentos_restantes,
            'letras_adivinadas' => json_decode($juego->adivinadas, true),
            'fecha' => $juego->created_at->format('Y-m-d H:i:s'),
            'resultado_final' => $this->mostrarPalabra($juego->palabra, json_decode($juego->adivinadas, true))
        ];
    }

    return response()->json([
        'total_juegos' => count($historial),
        'juegos_ganados' => $juegos_ganados,
        'juegos_perdidos' => $juegos_perdidos,
        'historial' => $historial
    ]);
}


    
}
