<?php

namespace App\Http\Requests;

use App\Models\Song;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Song::class);
    }

    public function rules(): array
    {
        $max = (int) config('cancionero.upload_max_mb', 20) * 1024;

        return ['title' => ['required', 'string', 'max:255'], 'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('songs', 'slug')], 'author' => ['nullable', 'string', 'max:255'], 'performer' => ['nullable', 'string', 'max:255'], 'musical_key' => ['nullable', 'string', 'max:30'], 'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('is_active', true)], 'liturgical_moment_id' => ['nullable', 'integer', Rule::exists('liturgical_moments', 'id')->where('is_active', true)], 'liturgical_seasons' => ['nullable', 'array'], 'liturgical_seasons.*' => ['integer', 'distinct', Rule::exists('liturgical_seasons', 'id')->where('is_active', true)], 'notes' => ['nullable', 'string'], 'source' => ['nullable', 'string', 'max:255'], 'video_url' => ['nullable', 'string', 'max:2048', 'url', function (string $attribute, mixed $value, Closure $fail): void { if (! $this->isYoutubeUrl((string) $value)) { $fail('El video debe ser una URL de YouTube o youtu.be.'); } }], 'is_active' => ['required', 'boolean'], 'files' => ['nullable', 'array', 'max:30'], 'files.*' => ['file', "max:{$max}", 'mimetypes:image/jpeg,image/png,image/webp,application/pdf', 'extensions:jpg,jpeg,png,webp,pdf']];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $pdfs = collect($this->file('files', []))->filter(fn ($file) => $file?->getMimeType() === 'application/pdf')->count();
            if ($pdfs > 1) {
                $validator->errors()->add('files', 'Solo se permite un PDF por carga.');
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    private function isYoutubeUrl(string $value): bool
    {
        $host = strtolower((string) parse_url($value, PHP_URL_HOST));

        return in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtu.be', 'www.youtu.be', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true);
    }
}
