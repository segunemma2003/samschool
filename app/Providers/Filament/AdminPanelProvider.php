<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use TomatoPHP\FilamentSubscriptions\Filament\Pages\Billing;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use TomatoPHP\FilamentTenancy\FilamentTenancyPlugin;
use App\Plugins\CustomFilamentTenancyPlugin;
use Filament\Enums\ThemeMode;
use Joaopaulolndev\FilamentGeneralSettings\FilamentGeneralSettingsPlugin;
use TomatoPHP\FilamentSubscriptions\FilamentSubscriptionsProvider;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->favicon(asset('latest/image/logo-head.png'))
            ->defaultThemeMode(ThemeMode::Dark)
            ->brandLogo(asset('latest/image/FSSLOGO1-1.png'))
            ->pages([
                Pages\Dashboard::class,
                Billing::class
            ])->tenantBillingProvider(new FilamentSubscriptionsProvider())
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])->plugin(CustomFilamentTenancyPlugin::make()->panel('app')
            ->allowImpersonate(),

        )->plugins([
        //     \TomatoPHP\FilamentSettingsHub\FilamentSettingsHubPlugin::make()
        // ->allowLocationSettings()
        // ->allowSiteSettings()
        // ->allowSocialMenuSettings(),
            \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            FilamentGeneralSettingsPlugin::make(),
            \TomatoPHP\FilamentSubscriptions\FilamentSubscriptionsPlugin::make(),
            \TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin::make(),
            \Ercogx\FilamentOpenaiAssistant\OpenaiAssistantPlugin::make(),
            \TomatoPHP\FilamentPWA\FilamentPWAPlugin::make()
        ]);
    }
}
