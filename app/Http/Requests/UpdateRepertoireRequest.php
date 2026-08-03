<?php

namespace App\Http\Requests;

use App\Models\Repertoire;
use Illuminate\Validation\Rule;

class UpdateRepertoireRequest extends StoreRepertoireRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('repertoire'));
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $repertoire = $this->route('repertoire');
        $rules['slug'] = ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('repertoires', 'slug')->ignore($repertoire instanceof Repertoire ? $repertoire->id : $repertoire)];

        return $rules;
    }
}
