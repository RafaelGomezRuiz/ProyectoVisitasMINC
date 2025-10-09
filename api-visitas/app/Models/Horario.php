<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;
    protected $fillable = [
        'localidad_id',
        'dia_semana',
        'hora_apertura',
        'hora_cierre',
    ];

    // Relación: Un horario pertenece a una localidad
    public function localidad()
    {
        return $this->belongsTo(Localidad::class);
    }
}
