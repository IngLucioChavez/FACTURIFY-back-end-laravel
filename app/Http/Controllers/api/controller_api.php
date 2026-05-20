<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;
use App\Models\Conversacion;
use App\Models\Mensaje;

class controller_api extends Controller
{
    // GET
    public function saludo(){
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
    public function validarSesion(){
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
    public function obtenerMensajesConversacion(Int $conversacionId)
    {
        try {

            // Usuario autenticado
            $usuarioId = Auth::guard('api')->id();

            // Buscar conversación
            $conversacion = Conversacion::find($conversacionId);

            // Verificar existencia
            if (!$conversacion) {

                return response()->json([
                    'message' => 'Conversación no encontrada'
                ], 404);
            }

            // Verificar acceso
            $pertenece = (
                $conversacion->usuario_1_id == $usuarioId
                ||
                $conversacion->usuario_2_id == $usuarioId
            );

            if (!$pertenece) {

                return response()->json([
                    'message' => 'No autorizado'
                ], 403);
            }

            // Obtener mensajes
            $mensajes = Mensaje::with('usuario')

                ->where('conversacion_id', $conversacionId)

                ->orderBy('created_at', 'asc')

                ->get();

            return response()->json([
                'conversacion' => $conversacion,
                'mensajes' => $mensajes
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Error al obtener mensajes',
                'error' => $e->getMessage()
            ], 500);
        }
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

            // ordenando ids menor y mayor respectivamente
            $usuario1 = min($usuarioAuth, $usuarioReceptor);
            $usuario2 = max($usuarioAuth, $usuarioReceptor);

            // Crear conversación
            $conversacion = Conversacion::create([
                'usuario_1_id' => $usuario1,
                'usuario_2_id' => $usuario2
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
    public function enviarMensaje(Request $request)
    {
        try {

            $request->validate([
                'conversacion_id' => 'required|exists:conversaciones,id',
                'mensaje' => 'required|string'
            ]);

            // Usuario autenticado
            $usuarioId = auth('api')->id();

            // Buscar conversación
            $conversacion = Conversacion::find(
                $request->conversacion_id
            );

            // Verificar que pertenece a la conversación
            $pertenece = (
                $conversacion->usuario_1_id == $usuarioId
                ||
                $conversacion->usuario_2_id == $usuarioId
            );

            if (!$pertenece) {

                return response()->json([
                    'message' => 'No tienes acceso a esta conversación'
                ], 403);
            }

            // Crear mensaje
            $mensaje = Mensaje::create([
                'conversacion_id' => $conversacion->id,
                'usuario_id' => $usuarioId,
                'mensaje' => $request->mensaje,
                'leido' => false
            ]);

            // Actualizar timestamp conversación
            $conversacion->touch();

            return response()->json([
                'message' => 'Mensaje enviado correctamente',
                'mensajeData' => $mensaje
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Error al enviar mensaje',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
