<?php

namespace App\Providers\Filament;

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

class FinancePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('finance')
            ->path('finance')
            ->login()
            ->brandLogo(getTenantLogo())
            ->favicon(getTenantLogo())
            ->sidebarWidth('15rem') /* Slimmer but readable */
            ->sidebarFullyCollapsibleOnDesktop(false)
            ->sidebarCollapsibleOnDesktop()
            ->collapsedSidebarWidth('4.5rem')
            ->brandLogoHeight('5rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Finance/Resources'), for: 'App\\Filament\\Finance\\Resources')
            ->discoverPages(in: app_path('Filament/Finance/Pages'), for: 'App\\Filament\\Finance\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Finance/Widgets'), for: 'App\\Filament\\Finance\\Widgets')
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
            ])->plugin(
                FilamentTenancyAppPlugin::make()

                )->plugins([
                    FilamentProgressbarPlugin::make()->color('#29b'),
                    LightSwitchPlugin::make(),
                ])
            ->authMiddleware([
                Authenticate::class,
            ])->viteTheme('resources/css/filament/finance/theme.css');
    }
}
