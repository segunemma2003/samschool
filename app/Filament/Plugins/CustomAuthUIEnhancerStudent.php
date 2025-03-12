<?php

namespace App\Filament\Plugins;

use App\Filament\Auth\StudentLogin;
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

class CustomAuthUIEnhancerStudent extends AuthUIEnhancerPlugin
{


public function register(Panel $panel): void
{
    // Override login to use TeacherLogin
    $panel->login(StudentLogin::class);

    // Call the parent register method (optional)
    parent::register($panel);
}

}
