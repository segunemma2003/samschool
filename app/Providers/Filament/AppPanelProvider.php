<?php

namespace App\Providers\Filament;

use Filament\Enums\ThemeMode;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
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
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use TomatoPHP\FilamentTenancy\FilamentTenancyAppPlugin;
use Joaopaulolndev\FilamentGeneralSettings\FilamentGeneralSettingsPlugin;
use Joaopaulolndev\FilamentGeneralSettings\Models\GeneralSetting;
use Stancl\Tenancy\Facades\Tenancy;
use TomatoPHP\FilamentSettingsHub\Models\Setting;
// use TomatoPHP\FilamentSettingsHub\Models\Setting;
use TomatoPHP\FilamentSettingsHub\Services\Contracts\SettingHold;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // $settings = Setting::where('name', 'site_logo')->first();
        // $settings =  Setting::where('id', 1)->first();
        // $logo = filament('filament-tenancy')->panel;
        // dd($logo);
        // dd(setting('site_logo'));
        // dd(tenant_setting('site_logo'));
        return $panel
            ->id('app')
            ->path('app')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\\Filament\\App\\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
            // ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            // ->brandLogo("https://res.cloudinary.com/iamdevmaniac/client_cat/".$logo)
            // ->favicon()
            // ->brandLogo(fn () =>
            //     Setting::where('name','site_logo')->first()
            // )
            ->defaultThemeMode(ThemeMode::Dark)
            // ->brandLogo(asset('latest/image/FSSLOGO1-1.png'))
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
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
            ])->plugin(
                FilamentTenancyAppPlugin::make())->plugins([
        //             \TomatoPHP\FilamentSettingsHub\FilamentSettingsHubPlugin::make()
        // ->allowLocationSettings()
        // ->allowSiteSettings()
        // ->allowSocialMenuSettings(),
                    \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(

                    ),
                    FilamentGeneralSettingsPlugin::make(
                        SettingHold::make()
                        ->order(2)
                        ->label('Site Settings')
                        ->icon('heroicon-o-globe-alt')
                        ->route('filament.app.pages.site-settings')
                        ->description('Name, Logo, Site Profile')
                        ->group('General'),
                    ),
                    \TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin::make(),
                    \Ercogx\FilamentOpenaiAssistant\OpenaiAssistantPlugin::make(),
                    \TomatoPHP\FilamentPWA\FilamentPWAPlugin::make()
                ]);
    }
}
