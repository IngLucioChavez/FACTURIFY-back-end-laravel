<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    protected $table = 'mensajes';

    protected $fillable = [
        'conversacion_id',
        'usuario_id',
        'mensaje',
        'leido'
    ];

    // =========================
    // RELACIONES
    // =========================

    public function conversacion()
    {
        return $this->belongsTo(Conversacion::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}