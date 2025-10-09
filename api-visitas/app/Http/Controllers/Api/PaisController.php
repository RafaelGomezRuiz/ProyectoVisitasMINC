<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pais;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaisController extends Controller
{
    public function index()
    {
        return Pais::latest()->paginate(10);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:paises,nombre',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $pais = Pais::create($validator->validated());
        return response()->json($pais, 201);
    }

    public function show(Pais $pai) // Laravel usa 'pai' por la convención de nombres
    {
        return response()->json($pai);
    }

    public function update(Request $request, Pais $pai)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255|unique:paises,nombre,' . $pai->id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $pai->update($validator->validated());
        return response()->json($pai);
    }

    public function destroy(Pais $pai)
    {
        $pai->delete();
        return response()->json(null, 204);
    }
}
