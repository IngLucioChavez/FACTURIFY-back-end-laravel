<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;

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

    //GET - validación de sesión JWT
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

    //POST - creación de nuevo usuario con cifrado de password en tabla usuarios
    public function register(Request $request)
    {
        try {

            // VALIDACIONES
            $request->validate([
                'nombre' => 'required|string|max:255',
                'email' => 'required|email|unique:usuarios,email',
                'password' => 'required|min:6'
            ]);

            // CREAR USUARIO
            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);

            // GENERAR TOKEN JWT
            $token = Auth::guard('api')->login($usuario);

            return response()->json([
                'message' => 'Usuario creado correctamente',
                'token' => $token,
                'user' => $usuario
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //GET - obtener conversaciones relacionadas del usuario
    public function threads(Request $request){

    }

    //GET - obtener información de una conversación relacionada con el usuario
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
