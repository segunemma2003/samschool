<?php

namespace App\Filament\Auth;

use Illuminate\Contracts\Support\Htmlable;

class StudentLogin extends CustomLogin
{
   public function getHeading(): string|Htmlable
   {
     return __('Student Login');
   }
}
