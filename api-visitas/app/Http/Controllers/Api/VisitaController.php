<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VisitaController extends Controller
{
    /**
     * Muestra una lista de visitas.
     * Puede ser filtrada por estado (ej: 'activa').
     * Los AgenteDeVisitas solo ven sus propias visitas (por localidad).
     */
    public function index(Request $request)
    {
        $query = Visita::with(['visitante', 'area']);
        $user = $request->user();

        // Si el usuario es AgenteDeVisitas, filtrar por su localidad
        if ($user && $user->hasRole('AgenteDeVisitas')) {
            $query->whereHas('area', function ($q) use ($user) {
                $q->where('localidad_id', $user->localidad_id);
            });
        }

        // **Ajuste clave: Filtrar por localidad a través de la relación con Area**
        if ($request->has('localidad_id')) {
            $query->whereHas('area', function ($q) use ($request) {
                $q->where('localidad_id', $request->localidad_id);
            });
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
            return $query->latest('hora_entrada')->get();
        }

        return $query->latest()->paginate(10);
    }

    /**
     * Proporciona datos agregados para el dashboard de estadísticas.
     * Puede ser filtrado por localidad_id.
     * Los AgenteDeVisitas solo ven estadísticas de su localidad.
     */
    public function stats(Request $request)
    {
        // Inicia las consultas base
        $visitsQuery = Visita::query();
        $user = $request->user();

        // Si el usuario es AgenteDeVisitas, filtrar por su localidad
        if ($user && $user->hasRole('AgenteDeVisitas')) {
            $visitsQuery->whereHas('area', function ($q) use ($user) {
                $q->where('localidad_id', $user->localidad_id);
            });
        }

        // **Ajuste clave: Aplica el filtro de localidad si existe**
        if ($request->has('localidad_id')) {
            $visitsQuery->whereHas('area', function ($q) use ($request) {
                $q->where('localidad_id', $request->localidad_id);
            });
        }

        // Clona la consulta base para diferentes métricas
        $totalVisits = (clone $visitsQuery)->count();
        $activeVisits = (clone $visitsQuery)->where('estado', 'activa')->count();
        $visitsToday = (clone $visitsQuery)->whereDate('fecha', today())->count();

        $mostVisitedAreaQuery = (clone $visitsQuery);
        $mostVisitedArea = $mostVisitedAreaQuery->select('area_id', DB::raw('count(*) as total'))
            ->groupBy('area_id')
            ->orderByDesc('total')
            ->with('area')
            ->first();

        return response()->json([
            'totalVisits' => $totalVisits,
            'activeVisits' => $activeVisits,
            'visitsToday' => $visitsToday,
            'mostVisitedArea' => $mostVisitedArea ? $mostVisitedArea->area->nombre : 'N/A',
            'mostVisitedAreaCount' => $mostVisitedArea ? $mostVisitedArea->total : 0,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'visitante_id' => 'required|exists:visitantes,id',
            'area_id' => 'required|exists:areas,id',
            'reserva_id' => 'nullable|exists:reservas,id',
            'fecha' => 'required|date',
            'hora_entrada' => 'required|date_format:H:i,H:i:s',
            'responsable' => 'nullable|string|max:255',
            'no_carnet' => 'nullable|string|max:50',
            'motivo' => 'nullable|string',
            'estado' => 'sometimes|required|in:activa,finalizada',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validated = $validator->validated();

        $visita = Visita::create($validated);
        return response()->json($visita, 201);
    }

    public function show(Visita $visita)
    {
        return $visita->load(['visitante', 'area', 'reserva']);
    }

    public function update(Request $request, Visita $visita)
    {
        // Valida que la hora de salida sea después de la de entrada
        $horaEntrada = $visita->hora_entrada;
        $validator = Validator::make($request->all(), [
            'area_id' => 'sometimes|required|exists:areas,id',
            'hora_salida' => "nullable|date_format:H:i,H:i:s|after:$horaEntrada",
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
