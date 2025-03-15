<?php

namespace App\Providers\Filament;

use App\Filament\Auth\AdminLogin;
use App\Filament\Auth\CustomLogin;
use App\Filament\Plugins\CustomAuthUIEnhancerAdmin;
use App\Http\Middleware\FilamentUnauthorizedRedirect;
use Vormkracht10\FilamentMails\FilamentMailsPlugin;
use Vormkracht10\FilamentMails\Facades\FilamentMails;
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use TomatoPHP\FilamentTenancy\FilamentTenancyAppPlugin;
use Joaopaulolndev\FilamentGeneralSettings\FilamentGeneralSettingsPlugin;
use Joaopaulolndev\FilamentGeneralSettings\Models\GeneralSetting;
use Stancl\Tenancy\Facades\Tenancy;
use TomatoPHP\FilamentSettingsHub\Models\Setting;
// use TomatoPHP\FilamentSettingsHub\Models\Setting;
use TomatoPHP\FilamentSettingsHub\Services\Contracts\SettingHold;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Tapp\FilamentAuthenticationLog\FilamentAuthenticationLogPlugin;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {

        return $panel
            ->id('app')
            ->path('app')
            ->login(AdminLogin::class)
            ->brandLogo(getTenantLogo())
            ->favicon(getTenantLogo())
            ->brandLogoHeight('5rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\\Filament\\App\\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
            ->discoverPages(in: app_path('Filament/Auth'), for: 'App\\Filament\\Auth')
            ->discoverPages(in: app_path('Filament/Plugins'), for: 'App\\Filament\\Plugins')
            // ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            // ->brandLogo("https://res.cloudinary.com/iamdevmaniac/client_cat/".setting('site_logo'))
            // ->favicon()
            // ->brandLogo(fn () =>
            //     Setting::where('name','site_logo')->first()
            // )
            ->defaultThemeMode(ThemeMode::Dark)
            // ->brandLogo(asset('latest/image/FSSLOGO1-1.png'))
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
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
            ->routes(fn() => FilamentMails::routes())

            ->authMiddleware([
                Authenticate::class,
            ])->plugin(
                FilamentMailsPlugin::make(),
                \TomatoPHP\FilamentDocs\FilamentDocsPlugin::make(),
                FilamentTenancyAppPlugin::make())->plugins([
                    FilamentGeneralSettingsPlugin::make(
                        SettingHold::make()
                        ->order(1)
                        ->label('Site Settings')
                        ->icon('heroicon-o-globe-alt')
                        ->route('filament.app.pages.site-settings')
                        ->description('Name, Logo, Site Profile')
                        ->group('General'),
                    )->setIcon('heroicon-o-cog'),
                    \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                    \TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin::make(),
                    \Ercogx\FilamentOpenaiAssistant\OpenaiAssistantPlugin::make(),
                    \TomatoPHP\FilamentPWA\FilamentPWAPlugin::make()
                ])->plugins([
                    LightSwitchPlugin::make(),
                    // FilamentAuthenticationLogPlugin::make(),
                    CustomAuthUIEnhancerAdmin::make()
                    ->emptyPanelBackgroundImageUrl(asset('images/swisnl/filament-backgrounds/curated-by-swis/27.jpg'))
                    ->emptyPanelBackgroundImageOpacity('100%') // Optional: Adjust opacity
                    ->formPanelPosition('right') // Form position
                    ->formPanelWidth('45%') // Adjust form width
                    ->showEmptyPanelOnMobile(false)

                ])->viteTheme('resources/css/filament/app/theme.css');
    }
}
