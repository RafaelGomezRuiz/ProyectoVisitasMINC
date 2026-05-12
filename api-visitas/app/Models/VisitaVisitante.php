<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitaVisitante extends Model
{
    //
    protected $fillable = ['visitante_id'];

    public function visitante()
    {
        return $this->belongsTo(Visitante::class);
    }
}
