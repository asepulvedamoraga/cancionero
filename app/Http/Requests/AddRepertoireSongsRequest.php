<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        ];
    }

    public function messages(): array
    {
        return [
            'song_ids.required' => 'Selecciona al menos una canción.',
            'song_ids.min' => 'Selecciona al menos una canción.',
        ];
    }
}
