<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoVisitante extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'es_extranjero'];

    // Relación: Un tipo de visitante puede tener muchos visitantes
    public function visitantes()
    {
        return $this->hasMany(Visitante::class);
    }
}
