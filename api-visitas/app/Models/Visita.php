<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    use HasFactory;
    protected $fillable = [
        'visitante_id',
        'area_id',
        'reserva_id',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'responsable',
        'no_carnet',
        'motivo',
        'estado',
    ];

    // Relación: Una visita pertenece a un visitante
    public function visitante()
    {
        return $this->belongsTo(Visitante::class);
    }

    // Relación: Una visita pertenece a un área
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    // Relación: Una visita puede pertenecer a una reserva
    public function reserva()
    {
        return $this->belongsTo(Reserva::class);
    }

}
