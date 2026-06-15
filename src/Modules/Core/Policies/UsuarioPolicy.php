<?php

namespace Modules\Core\Policies;

use App\Models\User;

class UsuarioPolicy
{
    public function before(User $user): ?bool
    {
        return $user->can('core.usuarios.gerenciar') ? true : null;
    }

    public function viewAny(User $user): bool { return false; }

    public function create(User $user): bool { return false; }

    public function update(User $user, User $alvo): bool
    {
        return ! $alvo->hasRole('admin') || $user->id === $alvo->id;
    }

    public function delete(User $user, User $alvo): bool
    {
        return $user->id !== $alvo->id && ! $alvo->hasRole('admin');
    }

    public function editarPermissoes(User $user, User $alvo): bool
    {
        return ! $alvo->hasRole('admin');
    }
}
