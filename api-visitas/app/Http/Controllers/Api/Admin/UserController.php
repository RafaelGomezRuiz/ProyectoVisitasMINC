<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /** Sólo admins pueden gestionar usuarios */
    protected function ensureAdmin(Request $request): void
    {
        if (!$request->user()->hasRole('Administrador')) {
            abort(403, 'Solo los administradores pueden realizar esta acción.');
        }
    }

    /** GET /api/admin/usuarios */
    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $perPage = (int) $request->input('per_page', 15);
        $search  = trim((string) $request->input('search', ''));

        $q = User::query()->with('roles', 'localidad');

        if ($search !== '') {
            $q->where(function ($qb) use ($search) {
                $qb->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('rol', 'like', "%{$search}%");
            });
        }

        $users = $perPage > 0
            ? $q->orderByDesc('id')->paginate($perPage)
            : $q->orderByDesc('id')->get();

        return response()->json($users);
    }

    /** POST /api/admin/usuarios */
    public function store(Request $request)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
            'rol'      => ['required', Rule::in([User::ROL_ADMIN, User::ROL_SUPERVISOR])],
            'localidad_id' => ['required', 'exists:localidades,id'],
            'roles'    => ['required', 'array', 'min:1'],
            'roles.*'  => ['string', 'exists:roles,nombre'],
        ]);

        $user = new User();
        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->rol   = $data['rol'];
        $user->localidad_id = $data['localidad_id'];
        $user->password = Hash::make($data['password']);
        $user->save();

        // Convertir nombres de roles a IDs y sincronizar
        if (!empty($data['roles'])) {
            $roleIds = \App\Models\Role::whereIn('nombre', $data['roles'])->pluck('id')->toArray();
            $user->roles()->sync($roleIds);
        }

        return response()->json($user->load('roles', 'localidad'), 201);
    }

    /** GET /api/admin/usuarios/{usuario} */
    public function show(Request $request, User $usuario)
    {
        $this->ensureAdmin($request);
        return response()->json($usuario->load('roles', 'localidad'));
    }

    /** PUT/PATCH /api/admin/usuarios/{usuario} */
    public function update(Request $request, User $usuario)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'name'     => ['sometimes', 'required', 'string', 'max:255'],
            'email'    => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'rol'      => ['sometimes', 'required', Rule::in([User::ROL_ADMIN, User::ROL_SUPERVISOR])],
            'localidad_id' => ['sometimes', 'required', 'exists:localidades,id'],
            'password' => ['nullable', 'string', 'min:8', 'max:100', 'confirmed'],
            'roles'    => ['sometimes', 'array', 'min:1'],
            'roles.*'  => ['string', 'exists:roles,nombre'],
        ]);

        if (array_key_exists('name', $data))  $usuario->name  = $data['name'];
        if (array_key_exists('email', $data)) $usuario->email = $data['email'];
        if (array_key_exists('rol', $data))   $usuario->rol   = $data['rol'];
        if (array_key_exists('localidad_id', $data)) $usuario->localidad_id = $data['localidad_id'];

        if (!empty($data['password'])) {
            $usuario->password = Hash::make($data['password']);
        }

        $usuario->save();

        // Sincronizar roles si se proporcionan
        if (array_key_exists('roles', $data) && !empty($data['roles'])) {
            $roleIds = \App\Models\Role::whereIn('nombre', $data['roles'])->pluck('id')->toArray();
            $usuario->roles()->sync($roleIds);
        }

        return response()->json($usuario->load('roles', 'localidad'));
    }

    /** DELETE /api/admin/usuarios/{usuario} */
    public function destroy(Request $request, User $usuario)
    {
        $this->ensureAdmin($request);

        if ($request->user()->id === $usuario->id) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario.'], 422);
        }

        // Limpiar roles antes de eliminar
        $usuario->roles()->detach();
        $usuario->delete();

        return response()->json(null, 204);
    }

    /** POST /api/admin/usuarios/{usuario}/password */
    public function changePassword(Request $request, User $usuario)
    {
        $this->ensureAdmin($request);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:100', 'confirmed'],
        ]);

        $usuario->password = Hash::make($data['password']);
        $usuario->save();

        return response()->json(['ok' => true]);
    }
}
