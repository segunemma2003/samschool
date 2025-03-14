<?php

namespace App\Providers\Filament;

use App\Filament\Auth\CustomLogin;
use App\Filament\Auth\GuardianLogin;
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
use TomatoPHP\FilamentTenancy\FilamentTenancyAppPlugin;

class OurparentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('parent')
            ->path('parent')
            ->login(GuardianLogin::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Ourparent/Resources'), for: 'App\\Filament\\Ourparent\\Resources')
            ->discoverPages(in: app_path('Filament/Ourparent/Pages'), for: 'App\\Filament\\Ourparent\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Ourparent/Widgets'), for: 'App\\Filament\\Ourparent\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
            ->plugins([
                LightSwitchPlugin::make(),
                    // CustomAuthUIEnhancerTeacher::make()
                    // ->emptyPanelBackgroundImageUrl(asset('images/swisnl/filament-backgrounds/curated-by-swis/12.jpg'))
                    // ->emptyPanelBackgroundImageOpacity('60%') // Optional: Adjust opacity
                    // ->formPanelPosition('right') // Form position
                    // ->formPanelWidth('40%') // Adjust form width
                    // ->showEmptyPanelOnMobile(false)

            ])
            ->authMiddleware([
                Authenticate::class,
            ])->plugin(
                FilamentTenancyAppPlugin::make())->plugins([]);
    }
}
