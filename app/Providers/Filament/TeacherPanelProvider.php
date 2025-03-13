<?php

namespace App\Providers\Filament;

use App\Filament\Auth\CustomLogin;
use App\Filament\Auth\TeacherLogin;
use App\Filament\Pages\Auth\MainCustomLogin;
use App\Filament\Plugins\CustomAuthUIEnhancer;
use App\Filament\Plugins\CustomAuthUIEnhancerTeacher;
use App\Filament\Teacher\Resources\AssignmentResource\Pages\ViewSubmittedAssignmentTeacher;
use App\Http\Middleware\FilamentUnauthorizedRedirect;
use App\Models\School;
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
use TomatoPHP\FilamentTenancy\FilamentTenancyAppPlugin;



class TeacherPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('teacher')
            ->path('teacher')
            ->brandLogo(getTenantLogo())
            ->favicon(getTenantLogo())
            ->brandLogoHeight('2rem')
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
            ])
            ->discoverWidgets(in: app_path('Filament/Teacher/Widgets'), for: 'App\\Filament\\Teacher\\Widgets')
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
            ])
            ->plugin(


                FilamentTenancyAppPlugin::make()


                )
                ->plugins([
                    CustomAuthUIEnhancerTeacher::make()
                    ->emptyPanelBackgroundColor(Color::hex('#f0f0f0'))
                    ->emptyPanelBackgroundImageUrl('https://images.pexels.com/photos/466685/pexels-photo-466685.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2'),


                ]);
    }




}
