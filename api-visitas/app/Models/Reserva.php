<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;
    protected $fillable = [
        'visitante_id',
        'area_id',
        'user_id',
        'fecha',
        'hora',
        'motivo',
        'estado',
    ];

    // Relación: Una reserva pertenece a un visitante
    public function visitante()
    {
        return $this->belongsTo(Visitante::class);
    }

    // Relación: Una reserva puede tener una visita asociada
    public function visita()
    {
        return $this->hasOne(Visita::class);
    }

    // Relación: Una reserva fue creada por un usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
