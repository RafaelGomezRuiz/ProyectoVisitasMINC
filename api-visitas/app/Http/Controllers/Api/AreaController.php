<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AreaController extends Controller
{
    public function index()
    {
        return Area::latest()->paginate(10);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:areas,nombre',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $area = Area::create($validator->validated());
        return response()->json($area, 201);
    }

    public function show(Area $area)
    {
        return response()->json($area);
    }

    public function update(Request $request, Area $area)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255|unique:areas,nombre,' . $area->id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $area->update($validator->validated());
        return response()->json($area);
    }

    public function destroy(Area $area)
    {
        $area->delete();
        return response()->json(null, 204);
    }
}
