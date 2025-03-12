<?php

namespace App\Filament\Plugins;

use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use App\Filament\Auth\TeacherLogin; // Your custom login
use DiogoGPinto\AuthUIEnhancer\Pages\Auth\AuthUiEnhancerRegister;
use DiogoGPinto\AuthUIEnhancer\Pages\Auth\EmailVerification\AuthUiEnhancerEmailVerificationPrompt;
use DiogoGPinto\AuthUIEnhancer\Pages\Auth\PasswordReset\AuthUiEnhancerRequestPasswordReset;
use DiogoGPinto\AuthUIEnhancer\Pages\Auth\PasswordReset\AuthUiEnhancerResetPassword;
use Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt;
use Filament\Pages\Auth\Login;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Filament\Pages\Auth\PasswordReset\ResetPassword;
use Filament\Pages\Auth\Register;
use Filament\Panel;

class CustomAuthUIEnhancerTeacher extends AuthUIEnhancerPlugin
{
//     public function register(Panel $panel): void
//    {
//        if ($panel->getLoginRouteAction() === Login::class) {
//            $panel
//                ->login(TeacherLogin::class);
//        }

//        if ($panel->getRegistrationRouteAction() === Register::class) {
//            $panel
//                ->registration(AuthUiEnhancerRegister::class);
//        }

//        if ($panel->getRequestPasswordResetRouteAction() === RequestPasswordReset::class && $panel->getResetPasswordRouteAction() === ResetPassword::class) {
//            $panel
//                ->passwordReset(AuthUiEnhancerRequestPasswordReset::class, AuthUiEnhancerResetPassword::class);

//        }

//        if ($panel->getEmailVerificationPromptRouteAction() === EmailVerificationPrompt::class) {
//            $panel
//                ->emailVerification(AuthUiEnhancerEmailVerificationPrompt::class);
//        }
//    }

public function register(Panel $panel): void
{
    // Override login to use TeacherLogin
    $panel->login(TeacherLogin::class);

    // Call the parent register method (optional)
    parent::register($panel);
}

}
