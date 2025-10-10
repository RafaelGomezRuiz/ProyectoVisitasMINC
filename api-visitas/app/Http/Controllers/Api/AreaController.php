<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AreaController extends Controller
{
    /**
     * Muestra una lista de áreas.
     * Puede ser filtrada por localidad_id para la vista de detalle.
     */
    public function index(Request $request)
    {
        $query = Area::query()->with('localidad');

        // Filtra por localidad si se proporciona el ID
        if ($request->has('localidad_id')) {
            $query->where('localidad_id', $request->localidad_id);
            // Para la vista de detalle, generalmente no se necesita paginación
            return $query->latest()->get();
        }

        // Si no se filtra, devuelve la lista paginada
        return $query->latest()->paginate(10);
    }

    /**
     * Almacena una nueva área en la base de datos.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'localidad_id' => 'required|exists:localidades,id',
            'nombre' => [
                'required',
                'string',
                'max:255',
                // Asegura que el nombre sea único para la localidad específica
                Rule::unique('areas')->where(function ($query) use ($request) {
                    return $query->where('localidad_id', $request->localidad_id);
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $area = Area::create($validator->validated());
        return response()->json($area, 201);
    }

    /**
     * Muestra un área específica.
     */
    public function show(Area $area)
    {
        return $area->load('localidad');
    }

    /**
     * Actualiza un área específica.
     */
    public function update(Request $request, Area $area)
    {
        $localidadId = $request->input('localidad_id', $area->localidad_id);

        $validator = Validator::make($request->all(), [
            'localidad_id' => 'sometimes|required|exists:localidades,id',
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                // La validación unique debe ignorar el registro actual
                Rule::unique('areas')->where(function ($query) use ($localidadId) {
                    return $query->where('localidad_id', $localidadId);
                })->ignore($area->id),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $area->update($validator->validated());
        return response()->json($area);
    }

    /**
     * Elimina un área.
     */
    public function destroy(Area $area)
    {
        // Opcional: Validar si el área tiene visitas antes de borrar
        // if ($area->visitas()->exists()) {
        //     return response()->json(['message' => 'No se puede eliminar, el área tiene visitas registradas.'], 409);
        // }

        $area->delete();
        return response()->json(null, 204);
    }
}
