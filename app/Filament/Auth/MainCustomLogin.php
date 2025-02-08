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

            // Get current tenant from domain
            $tenant = $this->getTenantFromDomain();

            if (! $tenant) {
                Notification::make()
                    ->danger()
                    ->title('Invalid School Domain')
                    ->send();
                return null;
            }

            if (! Filament::auth()->attempt($credentials, $data['remember'] ?? false)) {
                $this->throwFailureValidationException();
            }

            $user = Filament::auth()->user();

            // Check if user belongs to this tenant/school
            if (! $this->userBelongsToTenant($user, $tenant)) {
                Filament::auth()->logout();
                Notification::make()
                    ->danger()
                    ->title('Access Denied')
                    ->body('You do not have access to this school.')
                    ->send();
                return null;
            }

            // Check panel access
            if (! $user->canAccessPanel(Filament::getCurrentPanel())) {
                Filament::auth()->logout();
                Notification::make()
                    ->danger()
                    ->title('Access Denied')
                    ->body('You do not have permission to access this panel.')
                    ->send();
                return null;
            }

            session()->regenerate();
            return app(LoginResponse::class);

        } catch (ValidationException $e) {
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


    protected function userBelongsToTenant($user, $tenant): bool
    {
        // Implement your logic to check if user belongs to the school/tenant
        // Example:
        return $user->school_id === $tenant->id;
        // Or if using many-to-many:
        // return $user->schools->contains($tenant->id);
    }
    protected function getTenantFromDomain(): ?School  // Replace School with your actual tenant model
    {
        $domain = request()->getHost();
        $subdomain = explode('.', $domain)[0];

        // Assuming you have a School model with a 'domain' or 'subdomain' column
        return School::where('domain', $subdomain)
            ->orWhere('subdomain', $subdomain)
            ->first();
    }

    protected function getCredentialsFromFormData(array $data): array
    {
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
