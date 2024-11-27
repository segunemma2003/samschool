<?php

namespace App\Filament\Auth;
use Illuminate\Contracts\Support\Htmlable;

class GuardianLogin extends CustomLogin
{
    public function getHeading(): string|Htmlable
    {
      return __('Parent Login');
    }
}
