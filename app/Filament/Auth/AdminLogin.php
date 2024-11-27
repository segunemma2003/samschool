<?php

namespace App\Filament\Auth;

use Illuminate\Contracts\Support\Htmlable;

class AdminLogin extends CustomLogin
{
   public function getHeading(): string|Htmlable
   {
     return __('Admin Login');
   }
}
