<?php

namespace App\Http\Controllers;

use App\Http\Requests\SocialRequest;
use App\Models\Social;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $socials = Social::where('usuario_id', $request->user()->id)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'cv_url' => $socials->firstWhere('url_cv', '!=', null)?->url_cv,
            'links' => $socials->values(),
        ]);
    }

    public function store(SocialRequest $request): JsonResponse
    {
        $data = $this->payload($request);

        $social = Social::create([
            ...$data,
            'usuario_id' => $request->user()->id,
        ]);

        return response()->json($social, 201);
    }

    public function update(SocialRequest $request, int $id): JsonResponse
    {
        $social = Social::where('usuario_id', $request->user()->id)->findOrFail($id);
        $social->update($this->payload($request));

        return response()->json($social->fresh());
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $social = Social::where('usuario_id', $request->user()->id)->findOrFail($id);
        $social->delete();

        return response()->json(['message' => 'Enlace social eliminado correctamente']);
    }

    private function payload(Request $request): array
    {
        $data = $request->validated();
        $cvFile = $request->file('cvFile') ?? $request->file('cv_file');

        if ($cvFile) {
            $data['url_cv'] = $cvFile->store('cv', 'public');
        }

        return $data;
    }
}
