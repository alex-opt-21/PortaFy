<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsuarioResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'       => $this->id,
            'nombre'   => $this->nombre,
            'apellido' => $this->apellido,
            'email'    => $this->email,
            'rol'      => $this->rol,
            //////////
            'biografia'          => $this->biografia,
            'fecha_nacimiento'   => $this->fecha_nacimiento,
            'ubicacion'          => $this->ubicacion,
            'foto_perfil'        => $this->foto_perfil,
            'perfil_completado'  => $this->perfil_completado ?? false,
        ];
    }
}
