<?php

namespace App\Http\Controllers;

use App\Http\Requests\SkillRequest;
use App\Models\Habilidad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $skills = Habilidad::where('usuario_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return response()->json($skills);
    }

    public function store(SkillRequest $request): JsonResponse
    {
        $skill = Habilidad::create([
            ...$request->validated(),
            'usuario_id' => $request->user()->id,
        ]);

        return response()->json($skill, 201);
    }

    public function update(SkillRequest $request, int $id): JsonResponse
    {
        $skill = Habilidad::where('usuario_id', $request->user()->id)->findOrFail($id);
        $skill->update($request->validated());

        return response()->json($skill);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $skill = Habilidad::where('usuario_id', $request->user()->id)->findOrFail($id);
        $skill->delete();

        return response()->json(['message' => 'Habilidad eliminada correctamente']);
    }
}
