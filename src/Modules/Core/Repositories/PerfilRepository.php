<?php

namespace Modules\Core\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class PerfilRepository
{
    public function listar(): Collection
    {
        return Role::withCount('users')->orderBy('name')->get();
    }

    public function criar(array $dados): Role
    {
        return Role::create([
            'name'       => $dados['name'],
            'label'      => $dados['label'],
            'guard_name' => 'web',
        ]);
    }

    public function atualizar(Role $perfil, array $dados): Role
    {
        $perfil->update($dados);
        return $perfil->fresh();
    }

    public function deletar(Role $perfil): void
    {
        $perfil->delete();
    }
}
