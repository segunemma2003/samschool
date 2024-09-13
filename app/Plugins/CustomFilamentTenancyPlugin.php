<?php

namespace App\Plugins;

use App\Filament\Resources\CustomTenantResource;
use Filament\Contracts\Plugin;
use Filament\Panel;
use TomatoPHP\FilamentTenancy\Filament\Resources\TenantResource;
use TomatoPHP\FilamentTenancy\Http\Middleware\ApplyPanelColorsMiddleware;
use TomatoPHP\FilamentTenancy\Http\Middleware\RedirectIfInertiaMiddleware;
use TomatoPHP\FilamentTenancy\FilamentTenancyPlugin;

class CustomFilamentTenancyPlugin extends FilamentTenancyPlugin
{

    public string $panel = "app";
    public bool $allowImpersonate = false;

    public function panel(string $panel): static
    {
        $this->panel = $panel;
        return $this;
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                CustomTenantResource::class
            ])
            ->middleware([
                RedirectIfInertiaMiddleware::class,
            ])
            ->persistentMiddleware(['universal'])
            ->domains([
                config('filament-tenancy.central_domain')
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return new static();
    }
}
