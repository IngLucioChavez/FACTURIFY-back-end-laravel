<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class controller_api extends Controller
{
    // GET
    public function saludo(Request $request){
        return [
            "message" => "saludo"
        ];
    }

    // POST - LOGIN JWT
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {

            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        return response()->json([
            'token' => $token,
            'type' => 'Bearer',
            'user' => auth('api')->user()
        ]);
    }
    //POST - logout finalización de sesión
    public function logout(){
        try {

            Auth::guard('api')->logout();

            return response()->json([
                'message' => 'Sesión cerrada correctamente'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'No se pudo cerrar sesión'
            ], 500);
        }
    }

    public function validarSesion(Request $request){
        try {

            if (!$user = Auth::guard('api')->user()) {

                return response()->json([
                    'authenticated' => false
                ], 401);
            }

            return response()->json([
                'authenticated' => true,
                'user' => $user
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'authenticated' => false,
                'message' => 'Token inválido'
            ], 401);
        }
    }

    //GET - obtener conversaciones relacionadas
    public function threads(Request $request){

    }

    //GET - obtener información de conversación
    public function threadsID(Request $request, $id){

    }

    //POST - creación de nueva conversación
    public function createThread(Request $request){

    }

    //POST - enviar respuesta 
    public function createThreadResponseID(Request $request){

    }

    //GET - mensajes no leídos 
    public function notifications(Request $request){

    }

}
