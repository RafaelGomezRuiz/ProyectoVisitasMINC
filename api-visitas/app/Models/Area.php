<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['nombre', 'localidad_id'];

    /**
     * Relación: Un área pertenece a una localidad.
     */
    public function localidad()
    {
        return $this->belongsTo(Localidad::class);
    }

    /**
     * Relación: Un área puede tener muchas visitas.
     */
    public function visitas()
    {
        return $this->hasMany(Visita::class);
    }
}
