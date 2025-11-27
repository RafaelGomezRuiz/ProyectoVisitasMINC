<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * GET /api/admin/roles
     * Obtener lista de todos los roles disponibles
     */
    public function index()
    {
        return response()->json(Role::all());
    }

    /**
     * GET /api/admin/roles/{role}
     * Obtener un rol específico
     */
    public function show(Role $role)
    {
        return response()->json($role);
    }
}
