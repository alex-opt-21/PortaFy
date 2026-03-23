<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function completar(Request $request)
    {
        try {
            $usuario = $request->user();

            $datos = [];

            if ($request->filled('biografia'))
                $datos['biografia'] = $request->biografia;

            if ($request->filled('universidad'))
                $datos['universidad'] = $request->universidad;

            if ($request->filled('carrera'))
                $datos['carrera'] = $request->carrera;

            if ($request->filled('ubicacion'))
                $datos['ubicacion'] = $request->ubicacion;

            // Manejo de foto
            if ($request->hasFile('foto_perfil')) {
                $path = $request->file('foto_perfil')->store('fotos_perfil', 'public');
                $datos['foto_perfil'] = $path;
            }

            $datos['perfil_completado'] = true;

            // Crea o actualiza el perfil
            Profile::updateOrCreate(
                ['usuario_id' => $usuario->id],
                $datos
            );

            return response()->json([
                'message' => 'Perfil completado correctamente',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function crearPerfilProfesional(Request $request)
{
    try {
        $usuario = $request->user();

        $profile = Profile::where('usuario_id', $usuario->id)->first();

        if (!$profile) {
            return response()->json([
                'message' => 'Primero debe completar su perfil básico'
            ], 400);
        }

        $datos = [];

        if ($request->filled('titulo'))
            $datos['titulo'] = $request->titulo;

        if ($request->filled('skills'))
            $datos['skills'] = $request->skills;

        if ($request->filled('github'))
            $datos['github'] = $request->github;

        if ($request->filled('linkedin'))
            $datos['linkedin'] = $request->linkedin;

        $profile->update($datos);

        return response()->json([
            'message' => 'Perfil profesional actualizado correctamente',
            'profile' => $profile
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage(),
        ], 500);
    }
}
}
