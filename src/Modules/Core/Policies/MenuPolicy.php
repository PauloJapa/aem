<?php

namespace Modules\Core\Policies;

use App\Models\User;
use Modules\Core\Models\Menu;

class MenuPolicy
{
    public function before(User $user): ?bool
    {
        return $user->can('core.menu.gerenciar') ? true : null;
    }

    public function viewAny(User $user): bool { return false; }

    public function view(User $user, Menu $menu): bool { return false; }

    public function create(User $user): bool { return false; }

    public function update(User $user, Menu $menu): bool { return false; }

    public function delete(User $user, Menu $menu): bool { return false; }

    public function reorder(User $user): bool { return false; }
}
