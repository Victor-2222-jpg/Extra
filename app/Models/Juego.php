<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Juego extends Model
{
    use HasFactory;
    protected $table = 'juego';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'adivinadas',
        'intentos',
        'status',
        'palabra',
        'intentos_restantes'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function historial()
    {
        return $this->hasMany(JuegoHistorial::class, 'juego_id');
    }

}
