<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\controller_api;

Route::get('/saludo', [controller_api::class,'saludo']);
Route::post('/login', [controller_api::class,'login']);
