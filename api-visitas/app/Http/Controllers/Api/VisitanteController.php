<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisitanteController extends Controller
{
    /**
     * Devuelve una lista paginada de visitantes.
     * Laravel maneja la respuesta JSON para la paginación automáticamente.
     */
    public function index()
    {
        return Visitante::with(['paisDeOrigen', 'tipoVisitante'])->latest()->paginate(10);
    }

    /**
     * Busca visitantes por su documento de identidad.
     */
    public function buscarPorDocumento(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'documento_identidad' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $visitantes = Visitante::where('documento_identidad', '=', $request->documento_identidad)
            ->with(['paisDeOrigen', 'tipoVisitante'])
            ->take(5)
            ->get();

        return response()->json($visitantes);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_doc' => 'required|in:cedula,pasaporte,otro',
            'documento_identidad' => 'required|string|unique:visitantes,documento_identidad',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'edad' => 'required|integer|min:0|max:120',
            'correo' => 'nullable|email',
            'sexo' => 'required|in:Masculino,Femenino,Otro',
            'pais_origen_id' => 'required|exists:paises,id',
            'tipo_visitante_id' => 'required|exists:tipo_visitantes,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $visitante = Visitante::create($validator->validated());

        return response()->json($visitante, 201);
    }

    public function show(Visitante $visitante)
    {
        return response()->json($visitante->load(['paisDeOrigen', 'tipoVisitante', 'reservas', 'visitas']));
    }

    public function update(Request $request, Visitante $visitante)
    {
        $validator = Validator::make($request->all(), [
            'tipo_doc' => 'sometimes|required|in:cedula,pasaporte,otro',
            'documento_identidad' => 'sometimes|required|string|unique:visitantes,documento_identidad,' . $visitante->id,
            'nombres' => 'sometimes|required|string|max:255',
            'apellidos' => 'sometimes|required|string|max:255',
            'edad' => 'sometimes|required|integer|min:0|max:120',
            'correo' => 'nullable|email|unique:visitantes,correo,' . $visitante->id,
            'sexo' => 'sometimes|required|in:Masculino,Femenino,Otro',
            'pais_origen_id' => 'sometimes|required|exists:paises,id',
            'tipo_visitante_id' => 'sometimes|required|exists:tipo_visitantes,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $visitante->update($validator->validated());

        return response()->json($visitante);
    }

    public function destroy(Visitante $visitante)
    {
        $visitante->delete();
        return response()->json(null, 204);
    }
}

