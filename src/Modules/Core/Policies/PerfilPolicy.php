<?php

namespace Modules\Core\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class PerfilPolicy
{
    public function before(User $user): ?bool
    {
        return $user->can('core.usuarios.gerenciar') ? true : null;
    }

    public function viewAny(User $user): bool { return false; }

    public function create(User $user): bool { return false; }

    public function update(User $user, Role $perfil): bool
    {
        return $perfil->name !== 'admin';
    }

    public function delete(User $user, Role $perfil): bool
    {
        return $perfil->name !== 'admin' && $perfil->users()->count() === 0;
    }
}
