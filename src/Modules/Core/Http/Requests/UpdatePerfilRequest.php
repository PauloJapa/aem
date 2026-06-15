<?php

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('perfil'));
    }

    public function rules(): array
    {
        $perfilId = $this->route('perfil')?->id;

        return [
            'name'  => ['required', 'string', 'max:50', "unique:roles,name,{$perfilId}", 'regex:/^[a-z0-9\-]+$/'],
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
