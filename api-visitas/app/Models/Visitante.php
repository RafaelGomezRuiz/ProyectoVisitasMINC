<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitante extends Model
{
    use HasFactory;
    protected $fillable = [
        'tipo_doc',
        'documento_identidad',
        'nombres',
        'apellidos',
        'edad',
        'correo',
        'telefono',
        'sexo',
        'pais_origen_id',
        'provincia',
        'tipo_visitante_id',
    ];

    // Relación: Un visitante pertenece a un país
    public function paisDeOrigen()
    {
        return $this->belongsTo(Pais::class, 'pais_origen_id');
    }

    // Relación: Un visitante pertenece a un tipo
    public function tipoVisitante()
    {
        return $this->belongsTo(TipoVisitante::class);
    }

    // Relación: Un visitante puede tener muchas reservas
    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }

    // Relación: Un visitante puede tener muchas visitas
    public function visitas()
    {
        return $this->hasMany(Visita::class);
    }
}
