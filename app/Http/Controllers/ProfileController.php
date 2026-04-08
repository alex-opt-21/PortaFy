<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    private function resolveProfession(Usuario $usuario, ?object $legacyProfile): string
    {
        $usuarioProfesion = trim((string) ($usuario->profesion ?? ''));
        if ($usuarioProfesion !== '') {
            return $usuarioProfesion;
        }

        return trim((string) ($legacyProfile->profesion ?? ''));
    }

    private function mediaUrl(?string $path): string
    {
        if (!$path) {
            return '';
        }

        $normalizedPath = ltrim(preg_replace('#^(public/|storage/)#', '', $path), '/');
        $baseUrl = request()->getSchemeAndHttpHost();

        return $baseUrl . '/storage/' . $normalizedPath;
    }

    private function validateProfileRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'foto_perfil' => ['nullable', 'image', 'max:2048'],
            'foto_portada' => ['nullable', 'image', 'max:2048'],
            'profesion' => ['nullable', 'string', 'max:255'],
            'nombre' => ['nullable', 'string', 'max:255'],
            'apellido' => ['nullable', 'string', 'max:255'],
            'biografia' => ['nullable', 'string', 'max:1000'],
        ], [
            'foto_perfil.image' => 'La foto de perfil debe ser una imagen valida.',
            'foto_perfil.max' => 'La foto de perfil supera el limite actual de 2 MB del servidor.',
            'foto_portada.image' => 'La portada debe ser una imagen valida.',
            'foto_portada.max' => 'La portada supera el limite actual de 2 MB del servidor.',
        ]);
    }

    private function getLegacyProfile(int $usuarioId): ?object
    {
        if (!Schema::hasTable('perfiles_usuarios')) {
            return null;
        }

        return DB::table('perfiles_usuarios')
            ->where('user_id', $usuarioId)
            ->first();
    }

    private function syncLegacyProfile(Usuario $usuario, array $overrides = []): void
    {
        if (!Schema::hasTable('perfiles_usuarios')) {
            return;
        }

        $legacyProfile = $this->getLegacyProfile($usuario->id);
        $hasProfesionColumn = Schema::hasColumn('perfiles_usuarios', 'profesion');

        $payload = [
            'nombre' => $overrides['nombre'] ?? $usuario->nombre,
            'apellido' => $overrides['apellido'] ?? $usuario->apellido,
            'ubicacion' => $overrides['ubicacion'] ?? $usuario->ubicacion,
            'fecha_nacimiento' => $overrides['fecha_nacimiento'] ?? $usuario->fecha_nacimiento,
            'foto_perfil' => $overrides['foto_perfil'] ?? $usuario->foto_perfil,
            'updated_at' => now(),
        ];

        if ($hasProfesionColumn) {
            $payload['profesion'] = $overrides['profesion'] ?? $this->resolveProfession($usuario, $legacyProfile);
        }

        $exists = DB::table('perfiles_usuarios')
            ->where('user_id', $usuario->id)
            ->exists();

        if ($exists) {
            DB::table('perfiles_usuarios')
                ->where('user_id', $usuario->id)
                ->update($payload);

            return;
        }

        DB::table('perfiles_usuarios')->insert([
            'user_id' => $usuario->id,
            'created_at' => now(),
            ...$payload,
        ]);
    }

    public function storeOrUpdate(Request $request)
    {
        try {
            $validator = $this->validateProfileRequest($request);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $usuario = $request->user();
            $datosUsuario = [];
            $legacyOverrides = [];
            $hasUsuarioProfesionColumn = Schema::hasColumn('usuarios', 'profesion');

            if ($request->filled('nombre'))
                $datosUsuario['nombre'] = $request->nombre;
            if ($request->filled('apellido'))
                $datosUsuario['apellido'] = $request->apellido;
            if ($request->filled('biografia'))
                $datosUsuario['biografia'] = $request->biografia;
            if ($request->filled('ubicacion'))
                $datosUsuario['ubicacion'] = $request->ubicacion;
            if ($request->filled('fecha_nacimiento'))
                $datosUsuario['fecha_nacimiento'] = $request->fecha_nacimiento;
            if ($request->has('profesion')) {
                $legacyOverrides['profesion'] = trim((string) $request->input('profesion', ''));
                if ($hasUsuarioProfesionColumn) {
                    $datosUsuario['profesion'] = $legacyOverrides['profesion'];
                }
            }

            if ($request->hasFile('foto_perfil')) {
                $datosUsuario['foto_perfil'] = $request->file('foto_perfil')
                    ->store('fotos_perfil', 'public');
            }
            if ($request->hasFile('foto_portada')) {
                $datosUsuario['foto_portada'] = $request->file('foto_portada')
                    ->store('fotos_portada', 'public');
            }

            $legacyOverrides = [...$datosUsuario, ...$legacyOverrides];

            if (!empty($datosUsuario)) {
                $usuario->update($datosUsuario);
                $usuario->refresh();
            }

            if (!empty($legacyOverrides)) {
                $this->syncLegacyProfile($usuario, $legacyOverrides);
            }

            // Redes sociales van a tabla 'social'
            foreach (['github', 'linkedin'] as $red) {
                if ($request->filled($red)) {
                    \App\Models\Social::updateOrCreate(
                        [
                            'usuario_id'        => $usuario->id,
                            'nombre_plataforma' => $red,
                        ],
                        ['url_plataforma' => $request->$red]
                    );
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Perfil actualizado correctamente',
                'data'    => $usuario->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function show(Request $request)
    {
        $usuario = $request->user();
        $legacyProfile = $this->getLegacyProfile($usuario->id);

        // Traer redes sociales
        $github   = \App\Models\Social::where('usuario_id', $usuario->id)
            ->where('nombre_plataforma', 'github')
            ->first();
        $linkedin = \App\Models\Social::where('usuario_id', $usuario->id)
            ->where('nombre_plataforma', 'linkedin')
            ->first();

        return response()->json([
            'nombre'             => $usuario->nombre          ?? '',
            'apellido'           => $usuario->apellido        ?? '',
            'email'              => $usuario->email           ?? '',
            'profesion'          => $this->resolveProfession($usuario, $legacyProfile),
            'biografia'          => $usuario->biografia       ?? '',
            'ubicacion'          => $usuario->ubicacion ?: ($legacyProfile->ubicacion ?? ''),
            'fecha_nacimiento'   => $usuario->fecha_nacimiento ?: ($legacyProfile->fecha_nacimiento ?? ''),
            'foto_perfil'        => $usuario->foto_perfil ?: ($legacyProfile->foto_perfil ?? ''),
            'foto_perfil_url'    => $this->mediaUrl($usuario->foto_perfil ?: ($legacyProfile->foto_perfil ?? '')),
            'foto_portada'       => $usuario->foto_portada    ?? '',
            'foto_portada_url'   => $this->mediaUrl($usuario->foto_portada ?? ''),
            'perfil_completado'  => $usuario->perfil_completado ?? 0,
            'github'             => $github?->url_plataforma  ?? '',
            'linkedin'           => $linkedin?->url_plataforma ?? '',
        ]);
    }
    public function completar(Request $request)
    {
        try {
            $validator = $this->validateProfileRequest($request);
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $usuario = $request->user();
            $datos = [];

            if ($request->filled('biografia'))
                $datos['biografia'] = $request->biografia;

            if ($request->filled('ubicacion'))
                $datos['ubicacion'] = $request->ubicacion;

            // foto perfil
            if ($request->hasFile('foto_perfil')) {
                $path = $request->file('foto_perfil')->store('fotos_perfil', 'public');
                $datos['foto_perfil'] = $path;
            }

            $datos['perfil_completado'] = 1;

            // guarda en usuarios
            $usuario->update($datos);
            $usuario->refresh();
            $this->syncLegacyProfile($usuario, $datos);

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
            $validator = $this->validateProfileRequest($request);
            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $usuario = $request->user();
            // Verificar que el perfil básico esté completo
            if (!$usuario->perfil_completado) {
                return response()->json([
                    'message' => 'Primero debe completar su perfil básico'
                ], 400);
            }
            // Actualizar campos de perfil en tabla 'usuarios'
            $datosUsuario = [];
            if ($request->filled('biografia'))
                $datosUsuario['biografia'] = $request->biografia;
            if ($request->filled('ubicacion'))
                $datosUsuario['ubicacion'] = $request->ubicacion;
            if ($request->filled('fecha_nacimiento'))
                $datosUsuario['fecha_nacimiento'] = $request->fecha_nacimiento;
            if ($request->hasFile('foto_perfil')) {
                $datosUsuario['foto_perfil'] = $request->file('foto_perfil')
                    ->store('fotos_perfil', 'public');
            }

            if (!empty($datosUsuario)) {
                $usuario->update($datosUsuario);
                $usuario->refresh();
                $this->syncLegacyProfile($usuario, $datosUsuario);
            }

            // Guardar redes sociales en tabla 'social'
            $redes = [
                'github'   => $request->github,
                'linkedin' => $request->linkedin,
            ];

            foreach ($redes as $plataforma => $url) {
                if (!empty($url)) {
                    \App\Models\Social::updateOrCreate(
                        [
                            'usuario_id'        => $usuario->id,
                            'nombre_plataforma' => $plataforma,
                        ],
                        ['url_plataforma' => $url]
                    );
                }
            }

            return response()->json([
                'message' => 'Perfil profesional actualizado correctamente',
                'usuario' => $usuario->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // 🔍 BÚSQUEDA DE USUARIOS
    public function searchUsers(Request $request)
    {
        try {
            $query = trim($request->query('q', ''));

            if ($query === '') {
                return response()->json([]);
            }

            // 🔹 Normalizar espacios múltiples
            $words = preg_split('/\s+/', $query);

            $usersQuery = Usuario::query();

            // 🔹 CASO 1: una sola palabra
            if (count($words) === 1) {
                $term = $words[0];

                $usersQuery->where(function ($q) use ($term) {
                    $q->where('nombre', 'LIKE', "%{$term}%")
                        ->orWhere('apellido', 'LIKE', "%{$term}%")
                        ->orWhereHas('habilidades', function ($h) use ($term) {
                            $h->where('nombre', 'LIKE', "%{$term}%");
                        });
                });
            }

            // 🔹 CASO 2: nombre + apellido
            elseif (count($words) === 2) {
                [$nombre, $apellido] = $words;
                $usersQuery->where('nombre', 'LIKE', "%{$nombre}%")
                    ->where('apellido', 'LIKE', "%{$apellido}%");
            }

            // 🔹 CASO 3: nombre + múltiples apellidos
            else {
                $nombre = array_shift($words);
                $apellido = implode(' ', $words);

                $usersQuery->where('nombre', 'LIKE', "%{$nombre}%")
                    ->where('apellido', 'LIKE', "%{$apellido}%");
            }

            // 🔹 Traer habilidades
            $users = $usersQuery
                ->with('habilidades')
                ->limit(20)
                ->get();

            // 🔹 Formato para frontend
            $result = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->nombre,
                    'lastName' => $user->apellido,
                    'photo' => $user->foto_perfil,
                    'bio' => $user->biografia,
                    'skills' => $user->habilidades->pluck('nombre')->values(),
                ];
            });

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error en la búsqueda',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
