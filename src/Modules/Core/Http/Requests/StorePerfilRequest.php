<?php

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \Spatie\Permission\Models\Role::class);
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:50', 'unique:roles,name', 'regex:/^[a-z0-9\-]+$/'],
            'label' => ['required', 'string', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'O nome técnico deve conter apenas letras minúsculas, números e hífens.',
        ];
    }
}
