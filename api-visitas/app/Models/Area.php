<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    protected $fillable = ['nombre'];

    // Relación: Un área puede tener muchas visitas
    public function visitas()
    {
        return $this->hasMany(Visita::class);
    }
}
