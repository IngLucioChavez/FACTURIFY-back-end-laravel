<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;
use App\Models\Conversacion;
use App\Models\Mensaje;
use OpenApi\Attributes as OA;

#[OA\Info(title: "API de mini sistema de mensajería", version: "1.0.0", description: "Documentación de desarrollo")]
class controller_api extends Controller
{   
    #[OA\Get(
        path: '/api/saludo',
        operationId: 'saludo',
        tags: ['Sistema'],
        summary: 'Endpoint de prueba del sistema',
        description: 'Permite verificar que la API está activa y respondiendo correctamente.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Respuesta exitosa',
                content: new OA\JsonContent(
                    type: 'object',
                    example: [
                        'message' => 'saludo'
                    ]
                )
            )
        ]
    )]
    // GET
    public function saludo(){
        return [
            "message" => "saludo"
        ];
    }

    #[OA\Post(
        path: '/api/login',
        operationId: 'loginUser',
        tags: ['Auth'],
        summary: 'Inicio de sesión de usuario',
        description: 'Autentica al usuario con email y password y retorna un token JWT.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'usuario@test.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: '123456'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login exitoso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: '100'),
                        new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'),
                        new OA\Property(property: 'type', type: 'string', example: 'Bearer'),
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            example: [
                                'id' => 1,
                                'name' => 'Juan Pérez',
                                'email' => 'usuario@test.com'
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Credenciales incorrectas',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: '0'),
                        new OA\Property(property: 'message', type: 'string', example: 'Credenciales incorrectas')
                    ]
                )
            )
        ]
    )]
    // POST - LOGIN JWT
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {

            return response()->json([
                'status'=> '0',
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        return response()->json([
            'status'=> '100',
            'token' => $token,
            'type' => 'Bearer',
            'user' => auth('api')->user()
        ]);
    }

    #[OA\Post(
        path: '/api/logout',
        operationId: 'logoutUser',
        tags: ['Auth'],
        summary: 'Cerrar sesión del usuario',
        description: 'Invalida el token JWT actual y cierra la sesión del usuario autenticado.',
        security: [
            ['bearerAuth' => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesión cerrada correctamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: '100'),
                        new OA\Property(property: 'message', type: 'string', example: 'Sesión cerrada correctamente')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Error interno al cerrar sesión',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: '0'),
                        new OA\Property(property: 'message', type: 'string', example: 'No se pudo cerrar sesión')
                    ]
                )
            )
        ]
    )]
    //POST - logout finalización de sesión
    public function logout(){
        try {

            Auth::guard('api')->logout();

            return response()->json([
                'status'=> '100',
                'message' => 'Sesión cerrada correctamente'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'=> '0',
                'message' => 'No se pudo cerrar sesión'
            ], 500);
        }
    }

    #[OA\Get(
        path: '/api/validarSesion',
        operationId: 'validateSession',
        tags: ['Auth'],
        summary: 'Validar sesión del usuario',
        description: 'Verifica si el token JWT es válido y devuelve la información del usuario autenticado.',
        security: [
            ['bearerAuth' => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesión válida',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: '100'),
                        new OA\Property(property: 'authenticated', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            example: [
                                'id' => 1,
                                'name' => 'Juan Pérez',
                                'email' => 'usuario@test.com'
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado o token inválido',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: '0'),
                        new OA\Property(property: 'authenticated', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Token inválido')
                    ]
                )
            )
        ]
    )]
    //GET - validación de sesión JWT
    public function validarSesion(){
        try {

            if (!$user = Auth::guard('api')->user()) {

                return response()->json([
                    'status'=> '0',
                    'authenticated' => false
                ], 401);
            }

            return response()->json([
                'status'=> '100',
                'authenticated' => true,
                'user' => $user
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'=> '0',
                'authenticated' => false,
                'message' => 'Token inválido'
            ], 401);
        }
    }

    #[OA\Post(
        path: '/api/registroUsuario',
        operationId: 'registerUser',
        tags: ['Auth'],
        summary: 'Registro de nuevo usuario',
        description: 'Crea un nuevo usuario en el sistema y retorna un token JWT automáticamente.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre', 'email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'nombre',
                        type: 'string',
                        example: 'Juan Pérez'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'usuario@test.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: '123456'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuario creado correctamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: '100'),
                        new OA\Property(property: 'message', type: 'string', example: 'Usuario creado correctamente'),
                        new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'),
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            example: [
                                'id' => 1,
                                'nombre' => 'Juan Pérez',
                                'email' => 'usuario@test.com'
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Error de validación',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Información Inválida'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            example: [
                                'email' => ['The email has already been taken.']
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Error interno del servidor',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: '0'),
                        new OA\Property(property: 'message', type: 'string', example: 'Error al crear usuario')
                    ]
                )
            )
        ]
    )]
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
                'status'=> '100',
                'message' => 'Usuario creado correctamente',
                'token' => $token,
                'user' => $usuario
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'status'=> '0',
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: '/api/obtenerMisConversaciones',
        operationId: 'getMyConversations',
        tags: ['Conversaciones'],
        summary: 'Obtener conversaciones del usuario autenticado',
        description: 'Devuelve todas las conversaciones donde el usuario autenticado participa.',
        security: [
            ['bearerAuth' => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de conversaciones',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: '100'),
                        new OA\Property(
                            property: 'conversaciones',
                            type: 'array',
                            items: new OA\Items(type: 'object')
                        )
                    ]
                )
            )
        ]
    )]    
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

        return response()->json([
            'status'=> count($conversaciones) > 0 ? '100': '0',
            'conversaciones' => $conversaciones
        ]);
    }

    #[OA\Get(
        path: '/api/obtenerMensajesConversacion/{id}/mensajes',
        operationId: 'getConversationMessages',
        tags: ['Conversaciones'],
        summary: 'Obtener mensajes de una conversación',
        description: 'Retorna la conversación y todos sus mensajes ordenados cronológicamente.',
        security: [
            ['bearerAuth' => []]
        ],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID de la conversación',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mensajes obtenidos correctamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: '100'),
                        new OA\Property(property: 'conversacion', type: 'object'),
                        new OA\Property(
                            property: 'mensajes',
                            type: 'array',
                            items: new OA\Items(type: 'object')
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Conversación no encontrada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: '0'),
                        new OA\Property(property: 'message', type: 'string', example: 'Conversación no encontrada')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'No autorizado'
            )
        ]
    )]
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
                    'status'=> '0',
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
                    'status'=> '0',
                    'message' => 'No autorizado'
                ], 403);
            }

            // Obtener mensajes
            $mensajes = Mensaje::with('usuario')

                ->where('conversacion_id', $conversacionId)

                ->orderBy('created_at', 'asc')

                ->get();

            return response()->json([
                'status'=> '100',
                'conversacion' => $conversacion,
                'mensajes' => $mensajes
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'=> '0',
                'message' => 'Error al obtener mensajes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: '/api/crearConversacion',
        operationId: 'createConversation',
        tags: ['Conversaciones'],
        summary: 'Crear nueva conversación',
        description: 'Crea una conversación entre el usuario autenticado y otro usuario.',
        security: [
            ['bearerAuth' => []]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['usuario_receptor_id'],
                properties: [
                    new OA\Property(
                        property: 'usuario_receptor_id',
                        type: 'integer',
                        example: 2,
                        description: 'ID del usuario con quien se quiere iniciar la conversación'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Conversación creada',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: '100'),
                        new OA\Property(property: 'message', type: 'string', example: 'Conversación creada correctamente'),
                        new OA\Property(property: 'conversacion', type: 'object')
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Error de validación o lógica',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: '0'),
                        new OA\Property(property: 'message', type: 'string', example: 'No puedes crear una conversación contigo mismo')
                    ]
                )
            )
        ]
    )]
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
                    'status'=> '0',
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
                    'status'=> '0',
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
                'status'=> '100',
                'message' => 'Conversación creada correctamente',
                'conversacion' => $conversacion
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'status'=> '0',
                'message' => 'Error al crear conversación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: '/api/enviarMensaje',
        operationId: 'sendMessage',
        tags: ['Mensajes'],
        summary: 'Enviar mensaje en una conversación',
        description: 'Permite enviar un mensaje dentro de una conversación existente.',
        security: [
            ['bearerAuth' => []]
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['conversacion_id', 'mensaje'],
                properties: [
                    new OA\Property(property: 'conversacion_id', type: 'integer', example: 1),
                    new OA\Property(property: 'mensaje', type: 'string', example: 'Hola, ¿cómo estás?')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Mensaje enviado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: '100'),
                        new OA\Property(property: 'message', type: 'string', example: 'Mensaje enviado correctamente'),
                        new OA\Property(property: 'mensajeData', type: 'object')
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Sin acceso a la conversación'
            )
        ]
    )]
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
                    'status'=> '0',
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
                'status'=> '100',
                'message' => 'Mensaje enviado correctamente',
                'mensajeData' => $mensaje
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'status'=> '0',
                'message' => 'Error al enviar mensaje',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
