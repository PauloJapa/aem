<?php

namespace Modules\Core\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Core\Models\Menu;
use Modules\Core\Policies\MenuPolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;

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
    }
}
