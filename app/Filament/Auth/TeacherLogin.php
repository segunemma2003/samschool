<?php

namespace App\Filament\Auth;
use Illuminate\Contracts\Support\Htmlable;

class TeacherLogin
{
    /**
     * Create a new class instance.
     */
    public function getHeading(): string|Htmlable
   {
     return __('Teacher Login');
   }
}
