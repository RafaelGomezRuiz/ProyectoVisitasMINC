<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{

    use HasFactory;
    protected $table = 'paises';
    protected $fillable = ['nombre'];

    // Relación: Un país tiene muchos visitantes
    public function visitantes()
    {
        return $this->hasMany(Visitante::class, 'pais_origen_id');
    }
}
