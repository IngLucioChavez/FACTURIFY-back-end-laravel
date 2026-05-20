<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    protected $table = 'usuarios';

    protected $fillable = [
        'nombre',
        'email',
        'password'
    ];

    protected $hidden = [
        'password'
    ];

    // =========================
    // JWT
    // =========================

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function conversacionesComoUsuario1()
    {
        return $this->hasMany(
            Conversacion::class,
            'usuario_1_id'
        );
    }

    public function conversacionesComoUsuario2()
    {
        return $this->hasMany(
            Conversacion::class,
            'usuario_2_id'
        );
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class);
    }
}