<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExperienceRequest;
use App\Models\Experience;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = Experience::where('usuario_id', $request->user()->id)
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->get();

        return response()->json($items);
    }

    public function store(ExperienceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tipo'] = $data['tipo'] === 'trabajo' ? 'laboral' : $data['tipo'];

        $experience = Experience::create([
            ...$data,
            'usuario_id' => $request->user()->id,
        ]);

        return response()->json($experience, 201);
    }

    public function update(ExperienceRequest $request, int $id): JsonResponse
    {
        $experience = Experience::where('usuario_id', $request->user()->id)->findOrFail($id);
        $data = $request->validated();
        $data['tipo'] = ($data['tipo'] ?? null) === 'trabajo' ? 'laboral' : ($data['tipo'] ?? null);
        $experience->update($data);

        return response()->json($experience);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $experience = Experience::where('usuario_id', $request->user()->id)->findOrFail($id);
        $experience->delete();

        return response()->json(['message' => 'Experiencia eliminada correctamente']);
    }
}
