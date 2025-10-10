<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HorarioController extends Controller
{
    /**
     * Muestra una lista de horarios.
     * Puede ser filtrada por localidad_id.
     */
    public function index(Request $request)
    {
        $query = Horario::query()->with('localidad');

        // Filtra por localidad si se proporciona el ID
        if ($request->has('localidad_id')) {
            $query->where('localidad_id', $request->localidad_id);
            // Ordena por día de la semana para una visualización lógica
            return $query->orderBy('dia_semana', 'asc')->get();
        }

        return $query->latest()->paginate(15);
    }

    /**
     * Almacena un nuevo horario.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'localidad_id' => 'required|exists:localidades,id',
            'dia_semana' => [
                'required',
                'integer',
                'between:1,7',
                // Asegurar que no se repita el día para la misma localidad
                Rule::unique('horarios')->where(function ($query) use ($request) {
                    return $query->where('localidad_id', $request->localidad_id);
                }),
            ],
            'hora_apertura' => 'required|date_format:H:i:s,H:i',
            'hora_cierre' => 'required|date_format:H:i:s,H:i|after:hora_apertura',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $horario = Horario::create($validator->validated());
        return response()->json($horario, 201);
    }

    /**
     * Muestra un horario específico.
     */
    public function show(Horario $horario)
    {
        return $horario->load('localidad');
    }

    /**
     * Actualiza un horario específico.
     */
    public function update(Request $request, Horario $horario)
    {
        // Obtiene la localidad_id de la petición o del modelo existente
        $localidadId = $request->input('localidad_id', $horario->localidad_id);

        $validator = Validator::make($request->all(), [
            'localidad_id' => 'sometimes|required|exists:localidades,id',
            'dia_semana' => [
                'sometimes',
                'required',
                'integer',
                'between:1,7',
                Rule::unique('horarios')->where(function ($query) use ($localidadId) {
                    return $query->where('localidad_id', $localidadId);
                })->ignore($horario->id),
            ],
            'hora_apertura' => 'sometimes|required|date_format:H:i:s,H:i',
            'hora_cierre' => 'sometimes|required|date_format:H:i:s,H:i|after:hora_apertura',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $horario->update($validator->validated());
        return response()->json($horario);
    }

    /**
     * Elimina un horario.
     */
    public function destroy(Horario $horario)
    {
        $horario->delete();
        return response()->json(null, 204);
    }
}
