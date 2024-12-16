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

    



    public function adminHistorialJuegos(Request $request)
{

    $query = Juego::with('user') 
        ->select('juego.*', 'users.name', 'users.email')
        ->join('users', 'juego.user_id', '=', 'users.id');

    $juegos = $query->orderBy('juego.created_at', 'desc')->get();

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
            'intentos_realizados' => env('MAX_ATTEMPTS', 5) - $juego->intentos_restantes,
            'intentos_restantes' => $juego->intentos_restantes,
            'letras_adivinadas' => json_decode($juego->adivinadas, true),
            'fecha' => $juego->created_at->format('Y-m-d H:i:s')
        ];
    }


    return response()->json([
        'historial' => $historial
    ]);
}

    public function DesactivarCuenta(int $id){
        $user = User::find($id);
        $user->cuenta_activa = false;
        $user->numero_verificacion = null;
        $user->role_id=1;
        $user->save();
        return response()->json(['message' => 'Cuenta desactivada'], 200);
    }


}
