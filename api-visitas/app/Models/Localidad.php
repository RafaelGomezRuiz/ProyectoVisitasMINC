<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Localidad extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre',
        'direccion',
        'region',
        'provincia',
        'municipio',
        'telefono',
        'costo_entrada',
    ];

    // Relación: Una localidad tiene muchos horarios
    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    // Relación: Una localidad tiene muchos usuarios (operadores)
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }
}
