<?php

namespace App\Providers\Filament;

use App\Http\Middleware\FilamentUnauthorizedRedirect;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use TomatoPHP\FilamentTenancy\FilamentTenancyAppPlugin;

class HostelPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('hostel')
            ->path('hostel')
            ->brandLogo(getTenantLogo())
            ->favicon(getTenantLogo())
            ->passwordReset()
            ->brandLogoHeight('5rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Hostel/Resources'), for: 'App\\Filament\\Hostel\\Resources')
            ->discoverPages(in: app_path('Filament/Hostel/Pages'), for: 'App\\Filament\\Hostel\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Hostel/Widgets'), for: 'App\\Filament\\Hostel\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                FilamentUnauthorizedRedirect::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])->plugin(
                FilamentTenancyAppPlugin::make()

                ) ->plugins([
                    FilamentProgressbarPlugin::make()->color('#29b'),
                    LightSwitchPlugin::make(),
                ]);
    }
}
