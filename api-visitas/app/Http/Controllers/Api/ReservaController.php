<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReservaController extends Controller
{
    public function index(Request $request)
    {
        $query = Reserva::query()->with('visitante');
        $user = $request->user();

        // Si el usuario es AgenteDeVisitas, solo ver sus propias reservas
        if ($user && $user->hasRole('AgenteDeVisitas')) {
            $query->where('user_id', $user->id);
        }

        // **Ajuste clave para encontrar reservas pendientes de un visitante**
        if ($request->has('visitante_id') && $request->has('estado')) {
            $query->where('visitante_id', $request->visitante_id)
                ->where('estado', $request->estado);

            // Devuelve la primera que encuentre o ninguna
            $reserva = $query->latest('fecha')->first();
            return response()->json($reserva);
        }

        return $query->latest()->paginate(10);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'visitante_id' => 'required|exists:visitantes,id',
            'area_id' => 'required|exists:areas,id',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => 'required|date_format:H:i',
            'motivo' => 'nullable|string',
            'estado' => 'sometimes|required|in:pendiente,confirmada,cancelada,utilizada',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validated = $validator->validated();
        // Asignar el usuario autenticado como creador
        $validated['user_id'] = $request->user()->id;

        $reserva = Reserva::create($validated);
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
            'area_id' => 'sometimes|required|exists:areas,id',
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
