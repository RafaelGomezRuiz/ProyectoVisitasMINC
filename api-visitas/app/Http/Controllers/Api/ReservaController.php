<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReservaController extends Controller
{
    public function index()
    {
        return Reserva::with('visitante')->latest()->paginate(10);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'visitante_id' => 'required|exists:visitantes,id',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required|date_format:H:i',
            'motivo' => 'nullable|string',
            'estado' => 'sometimes|required|in:pendiente,confirmada,cancelada,utilizada',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $reserva = Reserva::create($validator->validated());
        return response()->json($reserva, 201);
    }

    public function show(Reserva $reserva)
    {
        return $reserva->load(['visitante', 'visita']);
    }

    public function update(Request $request, Reserva $reserva)
    {
        $validator = Validator::make($request->all(), [
            'visitante_id' => 'sometimes|required|exists:visitantes,id',
            'fecha' => 'sometimes|required|date',
            'hora' => 'sometimes|required|date_format:H:i',
            'motivo' => 'nullable|string',
            'estado' => 'sometimes|required|in:pendiente,confirmada,cancelada,utilizada',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $reserva->update($validator->validated());
        return response()->json($reserva);
    }

    public function destroy(Reserva $reserva)
    {
        $reserva->delete();
        return response()->json(null, 204);
    }
}
