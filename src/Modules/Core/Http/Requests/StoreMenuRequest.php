<?php

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Core\Models\Menu;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label'      => ['required', 'string', 'max:80'],
            'icon'       => ['required', 'string', 'max:60', 'regex:/^pi pi-[a-z0-9-]+$/'],
            'parent_id'  => ['nullable', 'exists:menus,id'],
            'rota'       => ['nullable', 'string', 'max:120'],
            'permission' => ['nullable', 'string', 'max:120'],
            'ativo'      => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($this->parent_id) {
                $pai = Menu::find($this->parent_id);
                if ($pai && $pai->parent_id !== null) {
                    $v->errors()->add('parent_id', 'Não é permitido mais de 2 níveis de menu.');
                }
            }
        });
    }
}
