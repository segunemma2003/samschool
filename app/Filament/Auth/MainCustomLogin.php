<?php

namespace App\Filament\Pages\Auth;

use App\Models\School;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use DomainException;
use Filament\Forms\Components\TextInput;
use Illuminate\Validation\ValidationException;

class MainCustomLogin extends BaseLogin
{
    public $remember = false;
    public $loginField; // ✅ Define this property
    public $password;   // ✅ Also define password since it's used in form

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }

        $data = $this->form->getState();


        try {
            $credentials = $this->getCredentialsFromFormData($data);


            if (! Filament::auth()->attempt($credentials, $data['remember'] ?? false)) {

                $this->throwFailureValidationException();
            }

            $user = Filament::auth()->user();

            if (
                ($user instanceof FilamentUser) &&
                (! $user->canAccessPanel(Filament::getCurrentPanel()))
            ) {
                Filament::auth()->logout();

                $this->throwFailureValidationException();
            }
            session()->regenerate();
            Notification::make()
            ->success()
            ->title('Success')
            ->body("Successfully Logged in ")
            ->send();
            return app(LoginResponse::class);

        } catch (ValidationException $e) {
            Notification::make()
            ->danger()
            ->title('Authentication failed')
            ->body($e->getMessage())
            ->send();
            throw $e;
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Authentication failed')
                ->body($e->getMessage())
                ->send();
            return null;
        }
    }



    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'loginField' => __('filament-panels::pages/auth/login.messages.failed'), // ✅ Fix reference here
        ]);
    }


    protected function getCredentialsFromFormData(array $data): array
    {
        // dd($data);
        $loginField = $data['loginField'];

        // Check if input is email or username
        $loginType = filter_var($loginField, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $loginType => $loginField,
            'password' => $data['password'],
        ];
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        TextInput::make('loginField')
                            ->label('Email or Username')
                            ->required()
                            ->autocomplete()
                            ->extraInputAttributes(['tabindex' => 1]),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
            ),
        ];
    }
}
