<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisitaController extends Controller
{
    public function index()
    {
        return Visita::with(['visitante', 'area'])->latest()->paginate(10);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'visitante_id' => 'required|exists:visitantes,id',
            'area_id' => 'required|exists:areas,id',
            'reserva_id' => 'nullable|exists:reservas,id',
            'fecha' => 'required|date',
            'hora_entrada' => 'required|date_format:H:i',
            'edad' => 'required|integer|min:0',
            'responsable' => 'nullable|string|max:255',
            'no_carnet' => 'nullable|string|max:50',
            'motivo' => 'nullable|string',
            'estado' => 'sometimes|required|in:activa,finalizada',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $visita = Visita::create($validator->validated());
        return response()->json($visita, 201);
    }

    public function show(Visita $visita)
    {
        return $visita->load(['visitante', 'area', 'reserva']);
    }

    public function update(Request $request, Visita $visita)
    {
        $validator = Validator::make($request->all(), [
            'area_id' => 'sometimes|required|exists:areas,id',
            'hora_salida' => 'nullable|date_format:H:i|after:hora_entrada',
            'responsable' => 'nullable|string|max:255',
            'no_carnet' => 'nullable|string|max:50',
            'motivo' => 'nullable|string',
            'estado' => 'sometimes|required|in:activa,finalizada',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $visita->update($validator->validated());
        return response()->json($visita);
    }

    public function destroy(Visita $visita)
    {
        $visita->delete();
        return response()->json(null, 204);
    }
}
