<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplaceSongFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('song'));
    }

    public function rules(): array
    {
        $max = (int) config('cancionero.upload_max_mb', 20) * 1024;

        return ['file' => ['required', 'file', "max:{$max}", 'mimetypes:image/jpeg,image/png,image/webp,application/pdf', 'extensions:jpg,jpeg,png,webp,pdf']];
    }
}
