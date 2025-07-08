<?php
namespace App\Traits;
// Create a helper trait for teacher authentication

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait HasTeacherAuth
{
    protected static function getCachedTeacher(): ?Teacher
    {
        return cache()->remember(
            'teacher_auth_' . Auth::id(),
            300, // 5 minutes
            function() {
                $user = User::whereId(Auth::id())->first();
                return $user ? Teacher::whereEmail($user->email)->first() : null;
            }
        );
    }

    protected static function getCachedTeacherWithArm(): ?Teacher
    {
        return cache()->remember(
            'teacher_with_arm_' . Auth::id(),
            300,
            function() {
                $user = User::whereId(Auth::id())->first();
                return $user ? Teacher::with('arm')->whereEmail($user->email)->first() : null;
            }
        );
    }
}
