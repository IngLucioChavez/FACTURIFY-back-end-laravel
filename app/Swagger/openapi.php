<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *     title="API de Conversaciones",
 *     version="1.0.0",
 *     description="Documentación de la API con Laravel + JWT"
 * )
 *
 * @OA\Server(
 *     url="http://localhost/api",
 *     description="Servidor local"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */