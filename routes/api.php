<?php

use App\Http\Controllers\Administrador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\controlcontroller;
use App\Http\Controllers\JuegoController;
use GuzzleHttp\Middleware;

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
    Route::post('login', [AuthController::class, 'login'])->Middleware('rol');
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
    Route::get('activacion/{numero_cuenta}', [controlcontroller::class, 'index'])->name('activacion');
    Route::post('send-activation-code', [AuthController::class, 'sendActivationCode']);
    
}); 

Route::middleware(['auth.jwt','rol'])->group(function (){
    Route::post('juego', [JuegoController::class, 'iniciarJuego']);
    Route::get('juego', [JuegoController::class, 'MostrarDisponibles']);
    Route::post('juego/unirse/{juegoId}', [JuegoController::class, 'unirseAJuego']);
    Route::post('juego/{letra}', [JuegoController::class, 'jugar'])
    ->where('letra', '^[a-zA-Z]$');
    Route::post('juego/salir', [JuegoController::class, 'abandonarjuego']);
    Route::get('juego/resultados', [JuegoController::class, 'consultarResultados']);
    Route::post('juego/historial',[JuegoController::class,'historialJuegos']);

});




Route::middleware(['auth.jwt', 'admin'])->group(function () {
    Route::get('admin/juegos', [Administrador::class, 'adminHistorialJuegos']);
    Route::post('admin/{id}',[Administrador::class,'DesactivarCuenta'])
    ->where('id', '[0-9]+');
    Route::get('admin',[Administrador::class,'VerTodaaslaspartidas']);
});
