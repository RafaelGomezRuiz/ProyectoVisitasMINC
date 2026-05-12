<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visitante;
use App\Models\VisitaVisitante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisitanteController extends Controller
{

   public function clientesTotal()
{
    $visitantes = VisitaVisitante::with('visitante')
        ->latest()
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'Total de visitantes registrados',
        'total' => $visitantes->count(),
        'visitantes' => $visitantes,
    ], 200);
}



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
     * Ignora la búsqueda si el documento es null o "0" (para menores sin documento).
     */
    public function buscarPorDocumento(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'documento_identidad' => 'required|string|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Si el documento es "0" o null, no buscar (es para menores)
        if ($request->documento_identidad === '0' || $request->documento_identidad === null) {
            return response()->json([]);
        }

        $visitantes = Visitante::where('documento_identidad', '=', $request->documento_identidad)
            ->with(['paisDeOrigen', 'tipoVisitante'])
            ->take(5)
            ->get();

        return response()->json($visitantes);
    }


   
public function store(Request $request)
{
    $visitantesData = $request->all();

    // Si viene un solo objeto, lo convertimos en array
    if (isset($visitantesData['nombres'])) {
        $visitantesData = [$visitantesData];
    }

    $visitantesCreados = [];
    $errores = [];

    foreach ($visitantesData as $index => $visitanteData) {

        $rules = [
            'tipo_doc'          => 'required|in:cedula,pasaporte,otro',
            'nombres'           => 'required|string|max:255',
            'apellidos'         => 'required|string|max:255',
            'edad'              => 'required|integer|min:0|max:120',
            'correo'            => 'nullable|email',
            'sexo'              => 'required|in:Masculino,Femenino,Otro',
            'pais_origen_id'    => 'required|exists:paises,id',
            'tipo_visitante_id' => 'required|exists:tipo_visitantes,id',
        ];

        if (($visitanteData['tipo_doc'] ?? null) === 'otro') {
            $rules['documento_identidad'] = 'nullable|string';
        } else {
            $rules['documento_identidad'] = 'required|string|unique:visitantes,documento_identidad';
        }

        $validator = Validator::make($visitanteData, $rules);

        if ($validator->fails()) {
            $errores[$index] = $validator->errors();
            continue;
        }

        $data = $validator->validated();

        if (
            ($data['documento_identidad'] ?? null) === null ||
            (($data['documento_identidad'] ?? null) === '0' && $data['tipo_doc'] === 'otro')
        ) {
            $data['documento_identidad'] = null;
        }

        $visitante = Visitante::create($data);

        $visitantesCreados[] = $visitante;


        VisitaVisitante::create([
            'visitante_id' => $visitante->id,
        ]);
    }

    if (!empty($errores)) {
        return response()->json([
            'message'            => 'Algunos visitantes no pudieron ser registrados',
            'visitantes_creados' => $visitantesCreados,
            'errores'            => $errores,
        ], 400);
    }

    return response()->json([
        'message' => 'Visitantes registrados correctamente',
        'data'    => $visitantesCreados,
    ], 201);
}


























    public function show(Visitante $visitante)
    {
        return response()->json($visitante->load(['paisDeOrigen', 'tipoVisitante', 'reservas', 'visitas']));
    }

    public function update(Request $request, Visitante $visitante)
    {
        $rules = [
            'tipo_doc' => 'sometimes|required|in:cedula,pasaporte,otro',
            'nombres' => 'sometimes|required|string|max:255',
            'apellidos' => 'sometimes|required|string|max:255',
            'edad' => 'sometimes|required|integer|min:0|max:120',
            'correo' => 'nullable|email|unique:visitantes,correo,' . $visitante->id,
            'sexo' => 'sometimes|required|in:Masculino,Femenino,Otro',
            'pais_origen_id' => 'sometimes|required|exists:paises,id',
            'tipo_visitante_id' => 'sometimes|required|exists:tipo_visitantes,id',
        ];

        // Validación condicional para documento_identidad
        $tipoDivision = $request->input('tipo_doc', $visitante->tipo_doc);
        if ($tipoDivision === 'otro') {
            $rules['documento_identidad'] = 'sometimes|nullable|string';
        } else {
            $rules['documento_identidad'] = 'sometimes|required|string|unique:visitantes,documento_identidad,' . $visitante->id;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $validator->validated();
        // Si documento_identidad es null o "0" y tipo_doc es 'otro', dejarlo como null
        if (isset($data['documento_identidad']) && ($data['documento_identidad'] === null || $data['documento_identidad'] === '0') && $tipoDivision === 'otro') {
            $data['documento_identidad'] = null;
        }

        $visitante->update($data);

        return response()->json($visitante);
    }

    public function destroy(Visitante $visitante)
    {
        $visitante->delete();
        return response()->json(null, 204);
    }
}

