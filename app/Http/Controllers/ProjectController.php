<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Models\Proyecto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = Proyecto::where('usuario_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return response()->json($projects);
    }

    public function store(ProjectRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['imagen'] = $this->storeImage($request) ?? null;
        $data['estado'] = $data['estado'] ?? 'en_progreso';

        $project = Proyecto::create([
            ...collect($data)->except(['cover'])->toArray(),
            'usuario_id' => $request->user()->id,
        ]);

        return response()->json($project, 201);
    }

    public function update(ProjectRequest $request, int $id): JsonResponse
    {
        $project = Proyecto::where('usuario_id', $request->user()->id)->findOrFail($id);
        $data = $request->validated();

        $imagePath = $this->storeImage($request);
        if ($imagePath) {
            $data['imagen'] = $imagePath;
        }

        $project->update(collect($data)->except(['cover'])->toArray());

        return response()->json($project->fresh());
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $project = Proyecto::where('usuario_id', $request->user()->id)->findOrFail($id);
        $project->delete();

        return response()->json(['message' => 'Proyecto eliminado correctamente']);
    }

    private function storeImage(Request $request): ?string
    {
        $file = $request->file('imagen') ?? $request->file('cover');

        return $file ? $file->store('proyectos', 'public') : null;
    }
}
