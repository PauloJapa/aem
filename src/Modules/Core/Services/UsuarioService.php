<?php

namespace Modules\Core\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Modules\Core\Repositories\UsuarioRepository;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsuarioService
{
    public function __construct(private UsuarioRepository $usuarios) {}

    public function criar(array $dados): User
    {
        $perfil = Role::findByName($dados['perfil']);
        $usuario = $this->usuarios->criar(Arr::except($dados, ['perfil', 'password_confirmation']));
        $usuario->assignRole($perfil);

        activity()
            ->performedOn($usuario)
            ->withProperties(['perfil' => $perfil->name])
            ->log('usuario_criado');

        return $usuario;
    }

    public function atualizar(User $usuario, array $dados): User
    {
        if (isset($dados['perfil'])) {
            $perfil = Role::findByName($dados['perfil']);
            $usuario->syncRoles([$perfil]);
        }

        $campos = Arr::except($dados, ['perfil', 'password_confirmation']);

        // Senha é opcional no update — só inclui se preenchida
        if (empty($campos['password'])) {
            unset($campos['password']);
        }

        return $this->usuarios->atualizar($usuario, $campos);
    }

    public function desativar(User $usuario): void
    {
        if (! $usuario->ativo) {
            return;
        }
        $this->usuarios->desativar($usuario);
        activity()->performedOn($usuario)->log('usuario_desativado');
    }

    public function reativar(User $usuario): void
    {
        $this->usuarios->reativar($usuario);
        activity()->performedOn($usuario)->log('usuario_reativado');
    }

    public function salvarPermissoesCustomizadas(User $usuario, array $extras, array $bloqueadas): void
    {
        $todasNomes = array_unique(array_merge($extras, $bloqueadas));

        if (! empty($todasNomes)) {
            $existentes = Permission::whereIn('name', $todasNomes)->pluck('name')->toArray();
            $invalidas = array_diff($todasNomes, $existentes);
            if (! empty($invalidas)) {
                throw new \DomainException('Permissions inválidas: ' . implode(', ', $invalidas));
            }
        }

        $conflito = array_intersect($extras, $bloqueadas);
        if (! empty($conflito)) {
            throw new \DomainException('Uma permission não pode ser extra e bloqueada ao mesmo tempo.');
        }

        $usuario->syncPermissions($extras);
        $usuario->forceFill(['permissions_bloqueadas' => $bloqueadas])->save();

        activity()
            ->performedOn($usuario)
            ->withProperties(compact('extras', 'bloqueadas'))
            ->log('permissoes_customizadas_salvas');
    }
}
