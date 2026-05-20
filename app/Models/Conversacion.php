<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversacion extends Model
{
    protected $table = 'conversaciones';

    protected $fillable = [
        'usuario_1_id',
        'usuario_2_id'
    ];

    // =========================
    // RELACIONES
    // =========================

    public function usuario1()
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_1_id'
        );
    }

    public function usuario2()
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_2_id'
        );
    }

    public function mensajes()
    {
        return $this->hasMany(Mensaje::class);
    }
}