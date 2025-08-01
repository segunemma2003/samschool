<?php

namespace App\Providers\Filament;

use App\Filament\Auth\CustomLogin;
use App\Filament\Auth\TeacherLogin;
use App\Filament\Pages\Auth\MainCustomLogin;
use App\Filament\Plugins\CustomAuthUIEnhancer;
use App\Filament\Plugins\CustomAuthUIEnhancerTeacher;
use App\Filament\Teacher\Pages\Chat;
use App\Filament\Teacher\Resources\AssignmentResource\Pages\ViewSubmittedAssignmentTeacher;
use App\Http\Middleware\FilamentUnauthorizedRedirect;
use App\Models\School;
use App\Providers\MyImageProvider;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use Filament\Actions\Action;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Orion\FilamentGreeter\GreeterPlugin;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;
use Swis\Filament\Backgrounds\ImageProviders\MyImages;
use TheThunderTurner\FilamentLatex\FilamentLatexPlugin;
use TomatoPHP\FilamentTenancy\FilamentTenancyAppPlugin;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use Monzer\FilamentChatifyIntegration\ChatifyPlugin;

class TeacherPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('teacher')
            ->path('teacher')
            ->brandLogo(getTenantLogo())
            ->favicon(getTenantLogo())
            ->passwordReset()
            ->brandLogoHeight('5rem')
            ->sidebarWidth('15rem') /* Slimmer but readable */
            ->sidebarFullyCollapsibleOnDesktop(false)
            ->sidebarCollapsibleOnDesktop()
            ->collapsedSidebarWidth('4.5rem')
            ->login(TeacherLogin::class)
            // ->profile()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Teacher/Resources'), for: 'App\\Filament\\Teacher\\Resources')
            ->discoverPages(in: app_path('Filament/Teacher/Pages'), for: 'App\\Filament\\Teacher\\Pages')
            ->discoverPages(in: app_path('Filament/Auth'), for: 'App\\Filament\\Auth')
            ->discoverPages(in: app_path('Filament/Plugins'), for: 'App\\Filament\\Plugins')
            ->discoverClusters(in: app_path('Filament/Teacher/Clusters'), for: 'App\\Filament\\Teacher\\Clusters')
            ->pages([
                Pages\Dashboard::class,
                ViewSubmittedAssignmentTeacher::class,
                Chat::class
            ])
            ->discoverWidgets(in: app_path('Filament/Teacher/Widgets'), for: 'App\\Filament\\Teacher\\Widgets')
            ->widgets([

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
            ])
            ->plugin(
                FilamentTenancyAppPlugin::make()

                )
                ->plugins([
                    FilamentProgressbarPlugin::make()->color('#29b'),
                    LightSwitchPlugin::make(),
                    // ChatifyPlugin::make(),
                    CustomAuthUIEnhancerTeacher::make()
                    ->emptyPanelBackgroundImageUrl(asset('images/swisnl/filament-backgrounds/curated-by-swis/12.jpg'))
                    ->emptyPanelBackgroundImageOpacity('90%') // Optional: Adjust opacity
                    ->formPanelPosition('right') // Form position
                    ->formPanelWidth('45%') // Adjust form width
                    ->showEmptyPanelOnMobile(false)

                ])->viteTheme('resources/css/filament/teacher/theme.css');
    }
}
