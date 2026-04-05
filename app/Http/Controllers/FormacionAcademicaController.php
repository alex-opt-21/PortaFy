<?php

namespace App\Http\Controllers;

use App\Models\FormacionAcademica;
use Illuminate\Http\Request;

class FormacionAcademicaController extends Controller
{
    // Guardar nueva formación
    public function store(Request $request)
    {
        try {
            $usuario = $request->user();

            $formacion = FormacionAcademica::create([
                'usuario_id'      => $usuario->id,
                'tipo_formacion'  => $request->tipo_formacion,
                'institucion'     => $request->institucion,
                'nombre_carrera' => $request->nombre_carrera,
                'fecha_inicio'    => $request->fecha_inicio,
                'fecha_fin'       => $request->fecha_fin,
            ]);

            return response()->json([
                'message'   => 'Formación académica guardada correctamente',
                'formacion' => $formacion,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Obtener todas las formaciones del usuario
    public function index(Request $request)
    {
        try {
            $usuario = $request->user();

            $formaciones = FormacionAcademica::where('usuario_id', $usuario->id)->get();

            return response()->json([
                'formaciones' => $formaciones,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}



