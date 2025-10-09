<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HorarioController extends Controller
{
    public function index()
    {
        return Horario::with('localidad')->latest()->paginate(15);
    }

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
            'hora_apertura' => 'required|date_format:H:i',
            'hora_cierre' => 'required|date_format:H:i|after:hora_apertura',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $horario = Horario::create($validator->validated());
        return response()->json($horario, 201);
    }

    public function show(Horario $horario)
    {
        return $horario->load('localidad');
    }

    public function update(Request $request, Horario $horario)
    {
        $validator = Validator::make($request->all(), [
            'localidad_id' => 'sometimes|required|exists:localidades,id',
            'dia_semana' => [
                'sometimes',
                'required',
                'integer',
                'between:1,7',
                Rule::unique('horarios')->where(function ($query) use ($request) {
                    return $query->where('localidad_id', $request->localidad_id);
                })->ignore($horario->id),
            ],
            'hora_apertura' => 'sometimes|required|date_format:H:i',
            'hora_cierre' => 'sometimes|required|date_format:H:i|after:hora_apertura',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $horario->update($validator->validated());
        return response()->json($horario);
    }

    public function destroy(Horario $horario)
    {
        $horario->delete();
        return response()->json(null, 204);
    }
}
