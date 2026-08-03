<?php

namespace App\Http\Requests;

use App\Models\Song;
use Illuminate\Validation\Rule;

class UpdateSongRequest extends StoreSongRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('song'));
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $song = $this->route('song');
        $rules['slug'] = ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('songs', 'slug')->ignore($song instanceof Song ? $song->id : $song)];

        return $rules;
    }
}
