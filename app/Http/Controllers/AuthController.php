<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\ActivarCuenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\TwilioService; 
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
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

    public function login()
    {
        $credentials = request(['name', 'email', 'password']);
    
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
    
        $id = auth()->user()->id; 
    
        DB::table('tabla_tokens')->updateOrInsert([
            'user_id' => $id,
            'token' => $token
        ]);
    
        return response()->json([
            'token' => $token,
        ]);
    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    
     private function sendActivationCodeWhatsApp($phone, $activationCode)
     {
         try {
             
             $twilioService = new TwilioService();
     
             
             $message = "Tu código de activación es: $activationCode. "
                      . "Para poder activar tu cuenta, consume la siguiente "
                      . "ruta para activar tu cuenta: https://127.0.0.1:8000/api/activacion";
     
             
             $twilioService->sendWhatsAppMessage($phone, $message);
     
             
             Log::info("Código de activación enviado con éxito a: $phone");
     
         } catch (\Exception $e) {
             // Si ocurre un error, lo registramos en los logs
             Log::error("Error al enviar código de activación a $phone: " . $e->getMessage());
         }
     }

    public function activarCuenta(Request $request)
    {
        $request->validate([
            'numero_verificacion' => 'required|integer',
        ]);

        $user = auth()->user()->id; 
        $user->cuenta_activa = true;
        $user->save();
        return response()->json(['message' => 'Cuenta activada'], 200);
    }
    
}
