<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject; // <-- AÑADIR ESTA LÍNEA

class User extends Authenticatable implements JWTSubject // <-- IMPLEMENTAR LA INTERFAZ
{
    use HasFactory, Notifiable;

    public const ROL_ADMIN = 'admin';
    public const ROL_SUPERVISOR = 'supervisor';

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
        'localidad_id', // <-- AÑADE ESTA LÍNEA
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- MÉTODOS REQUERIDOS POR JWTSubject ---

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'rol' => $this->rol, // <-- Añadimos el rol a los datos del token
        ];
    }
    public function localidad()
    {
        return $this->belongsTo(Localidad::class);
    }

    // Relación: Un usuario tiene muchos roles
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    // Helper: verificar si el usuario tiene un rol
    public function hasRole($role)
    {
        return $this->roles()->where('nombre', $role)->exists();
    }

    // Helper: verificar si el usuario tiene alguno de varios roles
    public function hasAnyRole($roles)
    {
        $roleNames = is_array($roles) ? $roles : func_get_args();
        return $this->roles()->whereIn('nombre', $roleNames)->exists();
    }

    // Relación: Un usuario puede tener muchas visitas como creador
    public function visitas()
    {
        return $this->hasMany(Visita::class);
    }

    // Relación: Un usuario puede tener muchas reservas como creador
    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }
}

