<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderSongFilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('song'));
    }

    public function rules(): array
    {
        return ['files' => ['required', 'array', 'min:1'], 'files.*' => ['required', 'integer', 'distinct', 'exists:song_files,id']];
    }
}
