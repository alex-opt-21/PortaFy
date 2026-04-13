<?php

namespace App\Services;

use App\Http\Requests\ProfileRequest;
use App\Models\Social;
use App\Models\Usuario;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ProfileService
{
    private array $tableCache = [];

    private array $columnCache = [];

    public function __construct(private readonly PublicAssetUrlService $assetUrlService) {}

    public function show(Usuario $usuario): array
    {
        return Cache::remember(
            $this->profileCacheKey($usuario->id, 'show'),
            now()->addSeconds(20),
            function () use ($usuario) {
                $legacyProfile = $this->getLegacyProfile($usuario->id);
                $socials = $usuario->relationLoaded('sociales')
                    ? $usuario->sociales->keyBy('plataforma')
                    : Social::forUser($usuario->id)->get()->keyBy('plataforma');

                $fotoPerfilPath = $usuario->foto_perfil ?: ($legacyProfile->foto_perfil ?? '');
                $fotoPortadaPath = $usuario->foto_portada ?? '';
                $cvUrl = $usuario->url_cv ?? ($socials->firstWhere('url_cv', '!=', null)?->url_cv ?? '');

                return [
                    'nombre' => $usuario->nombre ?? '',
                    'apellido' => $usuario->apellido ?? '',
                    'email' => $usuario->email ?? '',
                    'profesion' => $this->resolveProfession($usuario, $legacyProfile),
                    'biografia' => $usuario->biografia ?? '',
                    'ubicacion' => $usuario->ubicacion ?: ($legacyProfile->ubicacion ?? ''),
                    'fecha_nacimiento' => $usuario->fecha_nacimiento ?: ($legacyProfile->fecha_nacimiento ?? ''),
                    'foto_perfil' => $fotoPerfilPath,
                    'foto_perfil_url' => $this->assetUrlService->fromStoragePath($fotoPerfilPath),
                    'foto_portada' => $fotoPortadaPath,
                    'foto_portada_url' => $this->assetUrlService->fromStoragePath($fotoPortadaPath),
                    'url_cv' => $cvUrl,
                    'perfil_completado' => $usuario->perfil_completado ?? 0,
                    'github' => $socials->get('github')?->url_plataforma ?? $socials->get('github')?->url ?? '',
                    'linkedin' => $socials->get('linkedin')?->url_plataforma ?? $socials->get('linkedin')?->url ?? '',
                ];
            }
        );
    }

    public function overview(Usuario $usuario): array
    {
        return Cache::remember(
            $this->profileCacheKey($usuario->id, 'overview'),
            now()->addSeconds(20),
            function () use ($usuario) {
                $usuario->load([
                    'habilidades' => fn ($query) => $query->orderByDesc('id'),
                    'experiences' => fn ($query) => $query->orderByDesc('fecha_inicio')->orderByDesc('id'),
                    'proyectos' => fn ($query) => $query->orderByDesc('id'),
                    'sociales' => fn ($query) => $query->orderByDesc('id'),
                    'formacionAcademica' => fn ($query) => $query->orderByDesc('fecha_inicio')->orderByDesc('id'),
                ]);

                $profile = $this->show($usuario);

                return [
                    'profile' => $profile,
                    'skills' => $usuario->habilidades->values(),
                    'experience' => $usuario->experiences->values(),
                    'projects' => $usuario->proyectos->values(),
                    'socials' => [
                        'cv_url' => $profile['url_cv'],
                        'links' => $usuario->sociales->values(),
                    ],
                    'formacion' => $usuario->formacionAcademica->values(),
                ];
            }
        );
    }

    public function storeOrUpdate(ProfileRequest $request): Usuario
    {
        return DB::transaction(function () use ($request) {
            $usuario = $request->user();
            $datosUsuario = $this->extractGeneralProfileData($request);
            $legacyOverrides = [...$datosUsuario];

            if ($datosUsuario !== []) {
                $usuario->update(Usuario::persistenceData($datosUsuario));
                $usuario->refresh();
            }

            if ($legacyOverrides !== []) {
                $this->syncLegacyProfile($usuario, $legacyOverrides);
            }

            $this->syncSocialLinks($request, $usuario);
            $this->forgetProfileCache($usuario->id);

            return $usuario->fresh();
        });
    }

    public function completar(ProfileRequest $request): void
    {
        DB::transaction(function () use ($request) {
            $usuario = $request->user();
            $datos = [];

            if ($request->filled('biografia')) {
                $datos['biografia'] = $request->input('biografia');
            }

            if ($request->filled('ubicacion')) {
                $datos['ubicacion'] = $request->input('ubicacion');
            }

            if ($request->hasFile('foto_perfil')) {
                $datos['foto_perfil'] = $request->file('foto_perfil')->store('fotos_perfil', 'public');
            }

            $datos['perfil_completado'] = 1;

            $usuario->update(Usuario::persistenceData($datos));
            $usuario->refresh();
            $this->syncLegacyProfile($usuario, $datos);
            $this->forgetProfileCache($usuario->id);
        });
    }

    public function crearPerfilProfesional(ProfileRequest $request): Usuario
    {
        return DB::transaction(function () use ($request) {
            $usuario = $request->user();

            if (! $usuario->perfil_completado) {
                throw new RuntimeException('Primero debe completar su perfil basico');
            }

            $datosUsuario = [];

            if ($request->filled('biografia')) {
                $datosUsuario['biografia'] = $request->input('biografia');
            }

            if ($request->filled('ubicacion')) {
                $datosUsuario['ubicacion'] = $request->input('ubicacion');
            }

            if ($request->filled('fecha_nacimiento')) {
                $datosUsuario['fecha_nacimiento'] = $request->input('fecha_nacimiento');
            }

            if ($request->hasFile('foto_perfil')) {
                $datosUsuario['foto_perfil'] = $request->file('foto_perfil')->store('fotos_perfil', 'public');
            }

            if ($datosUsuario !== []) {
                $usuario->update(Usuario::persistenceData($datosUsuario));
                $usuario->refresh();
                $this->syncLegacyProfile($usuario, $datosUsuario);
            }

            $this->syncSocialLinks($request, $usuario);
            $this->forgetProfileCache($usuario->id);

            return $usuario->fresh();
        });
    }

    private function extractGeneralProfileData(ProfileRequest $request): array
    {
        $data = [];

        foreach (['nombre', 'apellido', 'profesion', 'biografia', 'ubicacion', 'fecha_nacimiento'] as $field) {
            if ($request->filled($field)) {
                $data[$field] = $request->input($field);
            }
        }

        if ($request->hasFile('foto_perfil')) {
            $data['foto_perfil'] = $request->file('foto_perfil')->store('fotos_perfil', 'public');
        }

        if ($request->hasFile('foto_portada')) {
            $data['foto_portada'] = $request->file('foto_portada')->store('fotos_portada', 'public');
        }

        return $data;
    }

    private function syncSocialLinks(ProfileRequest $request, Usuario $usuario): void
    {
        foreach (['github', 'linkedin', 'google'] as $red) {
            if ($request->filled($red)) {
                Social::updateOrCreate(
                    Social::platformIdentity($usuario->id, $red),
                    Social::persistenceData(['plataforma' => $red, 'url' => $request->input($red)])
                );
            }
        }
    }

    private function getLegacyProfile(int $usuarioId): ?object
    {
        if (! $this->hasTable('usuarios')) {
            return null;
        }

        return DB::table('usuarios')
            ->where('id', $usuarioId)
            ->first();
    }

    private function syncLegacyProfile(Usuario $usuario, array $overrides = []): void
    {
        if (! $this->hasTable('usuarios')) {
            return;
        }

        $legacyProfile = $this->getLegacyProfile($usuario->id);

        $payload = [
            'nombre' => $overrides['nombre'] ?? $usuario->nombre,
            'apellido' => $overrides['apellido'] ?? $usuario->apellido,
            'ubicacion' => $overrides['ubicacion'] ?? $usuario->ubicacion,
            'fecha_nacimiento' => $overrides['fecha_nacimiento'] ?? $usuario->fecha_nacimiento,
            'foto_perfil' => $overrides['foto_perfil'] ?? $usuario->foto_perfil,
            'updated_at' => now(),
        ];

        if ($this->hasColumn('usuarios', 'profesion')) {
            $payload['profesion'] = $overrides['profesion'] ?? $this->resolveProfession($usuario, $legacyProfile);
        }

        if ($legacyProfile) {
            DB::table('usuarios')
                ->where('id', $usuario->id)
                ->update($payload);

            return;
        }

        DB::table('usuarios')->insert([
            'id' => $usuario->id,
            'created_at' => now(),
            ...$payload,
        ]);
    }

    private function hasTable(string $table): bool
    {
        if (! array_key_exists($table, $this->tableCache)) {
            $this->tableCache[$table] = Cache::rememberForever(
                "schema.table.{$table}",
                static fn () => Schema::hasTable($table)
            );
        }

        return $this->tableCache[$table];
    }

    private function hasColumn(string $table, string $column): bool
    {
        $cacheKey = "{$table}.{$column}";

        if (! array_key_exists($cacheKey, $this->columnCache)) {
            $this->columnCache[$cacheKey] = Cache::rememberForever(
                "schema.column.{$cacheKey}",
                fn () => $this->hasTable($table) && Schema::hasColumn($table, $column)
            );
        }

        return $this->columnCache[$cacheKey];
    }

    private function resolveProfession(Usuario $usuario, ?object $legacyProfile): string
    {
        $usuarioProfesion = trim((string) ($usuario->profesion ?? ''));

        if ($usuarioProfesion !== '') {
            return $usuarioProfesion;
        }

        return trim((string) ($legacyProfile->profesion ?? ''));
    }

    private function profileCacheKey(int $userId, string $suffix): string
    {
        return "profile.{$userId}.{$suffix}";
    }

    private function forgetProfileCache(int $userId): void
    {
        Cache::forget($this->profileCacheKey($userId, 'show'));
        Cache::forget($this->profileCacheKey($userId, 'overview'));
    }
}
