<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRepertoireSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('repertoire'));
    }

    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:2000'],
            'song_tone_id' => ['nullable', 'integer', Rule::exists('song_tones', 'id')],
        ];
    }
}
