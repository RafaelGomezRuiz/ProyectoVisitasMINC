<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Localidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocalidadController extends Controller
{
    public function index()
    {
        return Localidad::with('horarios')->latest()->paginate(10);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
            'region' => 'nullable|string',
            'provincia' => 'required|string',
            'municipio' => 'required|string',
            'telefono' => 'nullable|string',
            'costo_entrada' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $localidad = Localidad::create($validator->validated());
        return response()->json($localidad, 201);
    }

    public function show(Localidad $localidade)
    {
        return $localidade->load(['horarios', 'usuarios']);
    }

    public function update(Request $request, Localidad $localidade)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'direccion' => 'sometimes|required|string|max:255',
            'region' => 'nullable|string',
            'provincia' => 'sometimes|required|string',
            'municipio' => 'sometimes|required|string',
            'telefono' => 'nullable|string',
            'costo_entrada' => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $localidade->update($validator->validated());
        return response()->json($localidade);
    }

    public function destroy(Localidad $localidade)
    {
        $localidade->delete();
        return response()->json(null, 204);
    }
}
