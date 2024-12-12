<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Juego;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class Administrador extends Controller
{
    public function registerAdmin(Request $request)
    {
        $credentials = $request->only('email','name','password');
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100|unique:users',
            'name' => 'required',
            'password' => 'required|string|min:6',
            'telefono' => 'required'
            
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(),400);
        }
        $activacion = rand(100000, 999999);


        
        $user = User::create(array_merge(
            $validator->validate(),
            [
                'password' => bcrypt($request->password),
                'role_id' => 1,
                'numero_verificacion' => $activacion
            ]
            
        ));

          $informacion = 'Biendido a la plataforma de prueba';

          $this->sendActivationCodeWhatsApp('+5218713944040', $activacion);

         /* $url= URL::temporarySignedRoute('activacion', now()->addMinutes(5), ['id' => $user->id]);
        Mail::to($user->email)->send(new ActivarCuenta($user,$informacion,$url)); */

        return response()->json([
            
                'email' => $user->email,
                'name' => $user->name,
                'password' => $user->password
        ], 201);
    }

    public function desacrtivarCuentas(int $id){
        $user = User::find($id);
        $user->cuenta_activa = false;
        foreach ($user as $user) {
            $user->cuenta_activa = false;
            $user->numero_verificacion = null;
            $user->save();
        }
        return response()->json(['message' => 'Cuentas desactivadas'], 200);
    }

    public function VerTodaaslaspartidas(){
       
        $partidas = juego::all();
        return response()->json(['partidas' => $partidas], 200);
    }


    /*

    <?php
// En api.php agregar nueva ruta:
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('admin/juegos', [JuegoController::class, 'adminHistorialJuegos']);
});

// En JuegoController.php agregar:
public function adminHistorialJuegos(Request $request)
{
    // Verificar si es administrador
    if (auth()->user()->role_id !== 3) { // Asumiendo que 3 es el ID del rol admin
        return response()->json([
            'message' => 'No autorizado'
        ], 403);
    }

    $query = Juego::with('user') // Eager loading de la relación user
        ->select('juegos.*', 'users.name', 'users.email')
        ->join('users', 'juegos.user_id', '=', 'users.id');

    // Aplicar filtros si existen
    if ($request->has('estado')) {
        $query->where('juegos.status', $request->estado);
    }

    if ($request->has('fecha_inicio')) {
        $query->whereDate('juegos.created_at', '>=', $request->fecha_inicio);
    }

    if ($request->has('fecha_fin')) {
        $query->whereDate('juegos.created_at', '<=', $request->fecha_fin);
    }

    $juegos = $query->orderBy('juegos.created_at', 'desc')->get();

    $resumen = [];
    $historial = [];

    foreach ($juegos as $juego) {
        $historial[] = [
            'id' => $juego->id,
            'usuario' => [
                'nombre' => $juego->user->name,
                'email' => $juego->user->email
            ],
            'palabra' => $juego->palabra,
            'estado' => $juego->status,
            'intentos_realizados' => env('MAX_ATTEMPTS', 6) - $juego->intentos_restantes,
            'intentos_restantes' => $juego->intentos_restantes,
            'letras_adivinadas' => json_decode($juego->adivinadas, true),
            'fecha' => $juego->created_at->format('Y-m-d H:i:s'),
            'resultado_final' => $this->mostrarPalabra($juego->palabra, json_decode($juego->adivinadas, true))
        ];
    }

    // Estadísticas globales
    $estadisticas = [
        'total_juegos' => count($historial),
        'juegos_ganados' => $juegos->where('status', 'ganado')->count(),
        'juegos_perdidos' => $juegos->where('status', 'perdido')->count(),
        'juegos_en_progreso' => $juegos->where('status', 'en progreso')->count()
    ];

    return response()->json([
        'estadisticas' => $estadisticas,
        'historial' => $historial
    ]);
}


    */



    public function adminHistorialJuegos(Request $request)
{
    // Verificar si es administrador
    if (auth()->user()->role_id !== 3) { // Asumiendo que 3 es el ID del rol admin
        return response()->json([
            'message' => 'No autorizado'
        ], 403);
    }

    $query = Juego::with('user') // Eager loading de la relación user
        ->select('juegos.*', 'users.name', 'users.email')
        ->join('users', 'juegos.user_id', '=', 'users.id');

    
    if ($request->has('estado')) {
        $query->where('juegos.status', $request->estado);
    }

    if ($request->has('fecha_inicio')) {
        $query->whereDate('juegos.created_at', '>=', $request->fecha_inicio);
    }

    if ($request->has('fecha_fin')) {
        $query->whereDate('juegos.created_at', '<=', $request->fecha_fin);
    }

    $juegos = $query->orderBy('juegos.created_at', 'desc')->get();

    $resumen = [];
    $historial = [];

    foreach ($juegos as $juego) {
        $historial[] = [
            'id' => $juego->id,
            'usuario' => [
                'nombre' => $juego->user->name,
                'email' => $juego->user->email
            ],
            'palabra' => $juego->palabra,
            'estado' => $juego->status,
            'intentos_realizados' => env('MAX_ATTEMPTS', 6) - $juego->intentos_restantes,
            'intentos_restantes' => $juego->intentos_restantes,
            'letras_adivinadas' => json_decode($juego->adivinadas, true),
            'fecha' => $juego->created_at->format('Y-m-d H:i:s'),
            'resultado_final' => $this->mostrarPalabra($juego->palabra, json_decode($juego->adivinadas, true))
        ];
    }

    // Estadísticas globales
    $estadisticas = [
        'total_juegos' => count($historial),
        'juegos_ganados' => $juegos->where('status', 'ganado')->count(),
        'juegos_perdidos' => $juegos->where('status', 'perdido')->count(),
        'juegos_en_progreso' => $juegos->where('status', 'en progreso')->count()
    ];

    return response()->json([
        'estadisticas' => $estadisticas,
        'historial' => $historial
    ]);
}

    public function DesactivarCuenta(int $id){
        $user = User::find($id);
        $user->cuenta_activa = false;
        $user->numero_verificacion = null;
        $user->save();
        return response()->json(['message' => 'Cuenta desactivada'], 200);
    }


}
