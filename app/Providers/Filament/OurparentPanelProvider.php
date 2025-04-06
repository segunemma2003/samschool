<?php

namespace App\Providers\Filament;

use App\Filament\Auth\CustomLogin;
use App\Filament\Auth\GuardianLogin;
use App\Filament\Plugins\CustomAuthUIEnhancerGuardian;
use App\Http\Middleware\FilamentUnauthorizedRedirect;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use TomatoPHP\FilamentTenancy\FilamentTenancyAppPlugin;

class OurparentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('parent')
            ->path('parent')
            ->login(GuardianLogin::class)
            ->passwordReset()
            ->brandLogo(getTenantLogo())
            ->favicon(getTenantLogo())
            ->brandLogoHeight('5rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Ourparent/Resources'), for: 'App\\Filament\\Ourparent\\Resources')
            ->discoverPages(in: app_path('Filament/Ourparent/Pages'), for: 'App\\Filament\\Ourparent\\Pages')
            ->discoverPages(in: app_path('Filament/Auth'), for: 'App\\Filament\\Auth')
            ->discoverPages(in: app_path('Filament/Plugins'), for: 'App\\Filament\\Plugins')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Ourparent/Widgets'), for: 'App\\Filament\\Ourparent\\Widgets')
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
            ->plugin(
                FilamentTenancyAppPlugin::make()
                )
            ->plugins([
                FilamentProgressbarPlugin::make()->color('#29b'),
                LightSwitchPlugin::make(),
                    CustomAuthUIEnhancerGuardian::make()
                    ->emptyPanelBackgroundImageUrl(asset('images/swisnl/filament-backgrounds/curated-by-swis/7.jpg'))
                    ->emptyPanelBackgroundImageOpacity('90%') // Optional: Adjust opacity
                    ->formPanelPosition('right') // Form position
                    ->formPanelWidth('45%') // Adjust form width
                    ->showEmptyPanelOnMobile(false)

            ])
            ->authMiddleware([
                Authenticate::class,
            ])->plugin(
                FilamentTenancyAppPlugin::make())->viteTheme('resources/css/filament/ourparent/theme.css');
    }
}
