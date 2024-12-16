<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JuegoHistorial extends Model
{
    use HasFactory;

    protected $table = 'juego_historial';

    protected $fillable = [
        'juego_id',
        'user_id',
        'letra',
        'palabra_actual',
        'intentos_restantes',
        'acierto',
        'estado_juego',
    ];

    public function juego()
    {
        return $this->belongsTo(Juego::class);
    }
}
