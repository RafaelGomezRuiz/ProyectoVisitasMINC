<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoVisitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TipoVisitanteController extends Controller
{
    public function index()
    {
        return TipoVisitante::latest()->paginate(10);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:tipo_visitantes,nombre',
            'es_extranjero' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $tipoVisitante = TipoVisitante::create($validator->validated());
        return response()->json($tipoVisitante, 201);
    }

    public function show(TipoVisitante $tipos_visitante)
    {
        return response()->json($tipos_visitante);
    }

    public function update(Request $request, TipoVisitante $tipos_visitante)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255|unique:tipo_visitantes,nombre,' . $tipos_visitante->id,
            'es_extranjero' => 'sometimes|required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $tipos_visitante->update($validator->validated());
        return response()->json($tipos_visitante);
    }

    public function destroy(TipoVisitante $tipos_visitante)
    {
        $tipos_visitante->delete();
        return response()->json(null, 204);
    }
}
