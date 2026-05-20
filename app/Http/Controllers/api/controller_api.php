<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class controller_api extends Controller
{   
    //GET - test
    public function saludo(Request $request){
        return ["message"=>"saludo"];
    }
     
    //POST - logeo incio de sesión JWT
    public function login(Request $request){

        return DB::table("usuarios")
            ->count();

    }
    //POST - logout finalización de sesión
    public function logout(Request $request){
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
