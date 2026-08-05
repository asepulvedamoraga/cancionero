<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AddRepertoireSongsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('repertoire'));
    }

    public function rules(): array
    {
        return [
            'song_ids' => ['required', 'array', 'min:1', 'max:100'],
            'song_ids.*' => ['required', 'integer', 'distinct', Rule::exists('songs', 'id')->where('is_active', true)],
            'song_tones' => ['nullable', 'array'],
            'song_tones.*' => ['nullable', 'integer', Rule::exists('song_tones', 'id')],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $songIds = collect($this->input('song_ids', []))->map(fn ($id) => (int) $id)->filter();
            $toneMap = collect((array) $this->input('song_tones', []));

            foreach ($songIds as $songId) {
                if (! $toneMap->has((string) $songId) && ! $toneMap->has($songId)) {
                    continue;
                }

                $toneId = (int) ($toneMap->get((string) $songId) ?? $toneMap->get($songId));
                if ($toneId <= 0) {
                    continue;
                }

                $belongs = \App\Models\SongTone::query()->whereKey($toneId)->where('song_id', $songId)->exists();
                if (! $belongs) {
                    $validator->errors()->add('song_tones.'.$songId, 'La tonalidad seleccionada no corresponde a la canción.');
                }
            }
        }];
    }

    public function messages(): array
    {
        return [
            'song_ids.required' => 'Selecciona al menos una canción.',
            'song_ids.min' => 'Selecciona al menos una canción.',
        ];
    }
}
