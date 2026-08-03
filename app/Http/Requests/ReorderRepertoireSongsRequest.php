<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderRepertoireSongsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('repertoire'));
    }

    public function rules(): array
    {
        return [
            'songs' => ['required', 'array', 'min:1'],
            'songs.*' => ['required', 'integer', 'distinct'],
        ];
    }
}
