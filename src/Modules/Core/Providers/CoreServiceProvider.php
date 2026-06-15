<?php

namespace Modules\Core\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Modules\Core\Models\Menu;
use Modules\Core\Policies\MenuPolicy;
use Modules\Core\Policies\PerfilPolicy;
use Modules\Core\Policies\UsuarioPolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Spatie\Permission\Models\Role;

class CoreServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Core';

    protected string $nameLower = 'core';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();
        Gate::policy(Menu::class, MenuPolicy::class);
        Gate::policy(User::class, UsuarioPolicy::class);
        Gate::policy(Role::class, PerfilPolicy::class);
    }
}
