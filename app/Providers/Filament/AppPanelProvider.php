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
use TomatoPHP\FilamentSettingsHub\Services\Contracts\SettingHold;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
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
            ->sidebarWidth('15rem') /* Slimmer but readable */
            ->sidebarFullyCollapsibleOnDesktop(false)
            ->sidebarCollapsibleOnDesktop()
            ->brandLogoHeight('5rem')
            ->collapsedSidebarWidth('4.5rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\\Filament\\App\\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
            ->discoverPages(in: app_path('Filament/Auth'), for: 'App\\Filament\\Auth')
            ->discoverPages(in: app_path('Filament/Plugins'), for: 'App\\Filament\\Plugins')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->defaultThemeMode(ThemeMode::Dark)
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
            ->widgets([
                // Keep empty for performance
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
            ])
            ->plugin(FilamentTenancyAppPlugin::make())
            ->plugins($this->getOptimizedPlugins())
            ->viteTheme('resources/css/filament/app/theme.css');
    }

    private function getOptimizedPlugins(): array
    {
        // Always load lightweight plugins
        $plugins = [
            FilamentProgressbarPlugin::make()->color('#29b'),
            LightSwitchPlugin::make(),
            \TomatoPHP\FilamentPWA\FilamentPWAPlugin::make(),
        ];

        $user = Auth::user();

        // Debug: Log user info to help troubleshoot
        if ($user) {
            \Log::info('AppPanel User Info', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_type' => $user->user_type ?? 'not_set',
                'all_attributes' => $user->toArray()
            ]);
        }

        // More flexible admin detection
        $isAdmin = $this->isUserAdmin($user);

        if ($isAdmin) {
            \Log::info('Loading admin plugins for user', ['user_id' => $user->id]);

            $plugins[] = FilamentGeneralSettingsPlugin::make(
                SettingHold::make()
                ->order(1)
                ->label('Site Settings')
                ->icon('heroicon-o-globe-alt')
                ->route('filament.app.pages.site-settings')
                ->description('Name, Logo, Site Profile')
                ->group('General'),
            )->setIcon('heroicon-o-cog');

            $plugins[] = \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make();
            $plugins[] = \Ercogx\FilamentOpenaiAssistant\OpenaiAssistantPlugin::make();
        }

        // Media manager for admins and teachers
        if ($user && $this->userCanUploadFiles($user)) {
            $plugins[] = \TomatoPHP\FilamentMediaManager\FilamentMediaManagerPlugin::make();
        }

        // Auth enhancer only on login pages
        if (!auth()->check()) {
            $plugins[] = CustomAuthUIEnhancerAdmin::make()
                ->emptyPanelBackgroundImageUrl(asset('images/swisnl/filament-backgrounds/curated-by-swis/27.jpg'))
                ->emptyPanelBackgroundImageOpacity('100%')
                ->formPanelPosition('right')
                ->formPanelWidth('45%')
                ->showEmptyPanelOnMobile(false);
        }

        \Log::info('AppPanel plugins loaded', ['plugin_count' => count($plugins), 'is_admin' => $isAdmin]);

        return $plugins;
    }

    /**
     * More flexible admin detection
     */
    private function isUserAdmin($user): bool
    {
        if (!$user) {
            return false;
        }

        // Check multiple possible admin indicators
        return $user->user_type === 'admin' ||
               $user->email === 'myadmin@admin.com' ||
               str_contains(strtolower($user->email), 'admin') ||
               (method_exists($user, 'hasRole') && $user->hasRole('admin')) ||
               (method_exists($user, 'can') && $user->can('access_admin_panel'));
    }

    /**
     * Check if user can upload files
     */
    private function userCanUploadFiles($user): bool
    {
        if (!$user) {
            return false;
        }

        return in_array($user->user_type, ['admin', 'teacher']) ||
               $this->isUserAdmin($user);
    }
}
