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

class LibraryPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('library')
            ->path('library')
            ->brandLogo(getTenantLogo())
            ->favicon(getTenantLogo())
            ->passwordReset()
            ->sidebarWidth('15rem') /* Slimmer but readable */
            ->sidebarFullyCollapsibleOnDesktop(false)
            ->sidebarCollapsibleOnDesktop()
            ->collapsedSidebarWidth('4.5rem')
            ->brandLogoHeight('5rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Library/Resources'), for: 'App\\Filament\\Library\\Resources')
            ->discoverPages(in: app_path('Filament/Library/Pages'), for: 'App\\Filament\\Library\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Library/Widgets'), for: 'App\\Filament\\Library\\Widgets')
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
                ])->viteTheme('resources/css/filament/library/theme.css');
    }
}
