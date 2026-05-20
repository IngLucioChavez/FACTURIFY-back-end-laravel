<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;
use App\Models\Conversacion;

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
    public function obtenerMisConversaciones()
    {
        $usuarioId = Auth::guard('api')->id();

        $conversaciones = Conversacion::with([
            'usuario1',
            'usuario2'
        ])

        ->where(function ($query) use ($usuarioId) {

            $query->where('usuario_1_id', $usuarioId)
                ->orWhere('usuario_2_id', $usuarioId);

        })

        ->orderBy('updated_at', 'desc')

        ->get();

        return response()->json($conversaciones);
    }

    //GET - obtener información de una conversación relacionada con el usuario
    public function threadsID(Request $request, $id){

    }

    //POST - creación de nueva conversación
    public function crearConversacion(Request $request)
    {
        try {

            $request->validate([
                'usuario_receptor_id' => 'required|exists:usuarios,id'
            ]);

            // Usuario autenticado
            $usuarioAuth = Auth::guard('api')->id();

            $usuarioReceptor = $request->usuario_receptor_id;

            // Evitar conversación consigo mismo
            if ($usuarioAuth == $usuarioReceptor) {

                return response()->json([
                    'message' => 'No puedes crear una conversación contigo mismo'
                ], 400);
            }

            // Verificar si ya existe conversación
            $conversacion = Conversacion::where(function ($query) use ($usuarioAuth, $usuarioReceptor) {
                    $query->where('usuario_1_id', $usuarioAuth)
                        ->where('usuario_2_id', $usuarioReceptor);
                })
                ->orWhere(function ($query)
                    use ($usuarioAuth, $usuarioReceptor) {

                    $query->where('usuario_1_id', $usuarioReceptor)
                        ->where('usuario_2_id', $usuarioAuth);

                })
                ->first();

            // Si ya existe
            if ($conversacion) {

                return response()->json([
                    'message' => 'La conversación ya existe',
                    'conversacion' => $conversacion
                ]);
            }

            // Crear conversación
            $conversacion = Conversacion::create([
                'usuario_1_id' => $usuarioAuth,
                'usuario_2_id' => $usuarioReceptor
            ]);

            return response()->json([
                'message' => 'Conversación creada correctamente',
                'conversacion' => $conversacion
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Error al crear conversación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //POST - enviar respuesta 
    public function createThreadResponseID(Request $request){

    }

    //GET - mensajes no leídos 
    public function notifications(Request $request){

    }

}
