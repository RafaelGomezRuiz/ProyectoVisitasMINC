<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relación: Un rol tiene muchos usuarios
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }
}
