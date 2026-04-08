<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $tags = $this->input('tecnologias', $this->input('tags'));

        if (is_array($tags)) {
            $tags = implode(', ', array_unique(array_filter(array_map(
                fn ($tag) => $this->normalizeText($tag),
                $tags
            ))));
        }

        $this->merge([
            'titulo' => $this->normalizeText($this->input('titulo', $this->input('title'))),
            'descripcion' => $this->normalizeText($this->input('descripcion', $this->input('description'))),
            'tecnologias' => $tags,
            'url_demo' => $this->input('url_demo', $this->input('demoUrl')),
            'url_repositorio' => $this->input('url_repositorio', $this->input('repositoryUrl')),
        ]);
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'min:3', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:1500'],
            'tecnologias' => ['nullable', 'string', 'max:255'],
            'url_demo' => ['nullable', 'url', 'max:255'],
            'url_repositorio' => ['nullable', 'url', 'max:255'],
            'estado' => ['nullable', 'in:en_progreso,completado,pausado'],
            'imagen' => ['nullable', 'image', 'max:2048'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.min' => 'El titulo del proyecto es demasiado corto.',
            'url_demo.url' => 'La demo URL debe ser un enlace valido.',
            'url_repositorio.url' => 'El enlace del repositorio debe ser un URL valido.',
            'imagen.image' => 'La portada debe ser una imagen valida.',
            'cover.image' => 'La portada debe ser una imagen valida.',
            'imagen.max' => 'La portada no debe superar 2 MB.',
            'cover.max' => 'La portada no debe superar 2 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'titulo' => 'titulo',
            'descripcion' => 'descripcion',
            'tecnologias' => 'tecnologias',
            'url_demo' => 'demo URL',
            'url_repositorio' => 'repositorio URL',
            'estado' => 'estado del proyecto',
        ];
    }

    private function normalizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return preg_replace('/\s+/', ' ', trim($value));
    }
}
