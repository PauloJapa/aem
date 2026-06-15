<?php

namespace Modules\Core\Services;

use Modules\Core\Repositories\PerfilRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PerfilService
{
    public function __construct(private PerfilRepository $perfis) {}

    public function criar(array $dados): Role
    {
        $perfil = $this->perfis->criar($dados);
        activity()->performedOn($perfil)->log('perfil_criado');
        return $perfil;
    }

    public function atualizar(Role $perfil, array $dados): Role
    {
        $perfil = $this->perfis->atualizar($perfil, $dados);
        activity()->performedOn($perfil)->log('perfil_atualizado');
        return $perfil;
    }

    public function salvarPermissoes(Role $perfil, array $permissions): void
    {
        if (! empty($permissions)) {
            $validas = Permission::whereIn('name', $permissions)->pluck('name')->toArray();
            $invalidas = array_diff($permissions, $validas);
            if (! empty($invalidas)) {
                throw new \DomainException('Permissions inválidas: ' . implode(', ', $invalidas));
            }
        }

        $perfil->syncPermissions($permissions);

        activity()
            ->performedOn($perfil)
            ->withProperties(['permissions' => $permissions])
            ->log('permissoes_perfil_atualizadas');
    }

    public function deletar(Role $perfil): void
    {
        $count = $perfil->users()->count();
        if ($count > 0) {
            throw new \DomainException(
                "Perfil possui {$count} usuário(s) vinculado(s). Remova-os antes de excluir."
            );
        }
        activity()->performedOn($perfil)->log('perfil_deletado');
        $this->perfis->deletar($perfil);
    }
}
