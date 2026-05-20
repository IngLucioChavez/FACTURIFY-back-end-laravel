<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\controller_api;

Route::get('/saludo', [controller_api::class,'saludo']);
Route::post('/login', [controller_api::class,'login']);
Route::middleware('auth:api')->post('/logout', [controller_api::class, 'logout']);
Route::middleware('auth:api')->get('/validar-sesion',[controller_api::class, 'validarSesion']);
Route::post('/register', [controller_api::class, 'register']);
Route::middleware('auth:api')->get('/obtenerMisConversaciones',[controller_api::class, 'obtenerMisConversaciones']);
Route::middleware('auth:api')->post('/crearConversacion',[controller_api::class, 'crearConversacion']);
Route::middleware('auth:api')->post('/enviarMensaje',[controller_api::class, 'enviarMensaje']);
Route::middleware('auth:api')->get('/obtenerMensajesConversacion/{id}/mensajes',[controller_api::class, 'obtenerMensajesConversacion']);