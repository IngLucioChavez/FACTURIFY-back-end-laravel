<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\controller_api;

Route::controller(controller_api::class)->group(function () {

    // Públicas
    Route::get('saludo', 'saludo');
    Route::post('login', 'login');
    Route::post('registroUsuario', 'register');

    // Protegidas
    Route::middleware('auth:api')->group(function () {

        Route::post('logout', 'logout');
        Route::get('validarSesion', 'validarSesion');

        Route::get('obtenerMisConversaciones', 'obtenerMisConversaciones');
        Route::post('crearConversacion', 'crearConversacion');

        Route::post('enviarMensaje', 'enviarMensaje');

        Route::get(
            'obtenerMensajesConversacion/{id}/mensajes',
            'obtenerMensajesConversacion'
        );
    });
});