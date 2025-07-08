<?php
namespace App\Traits;
// Create a helper trait for teacher authentication

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait HasTeacherAuth
{
    protected static function getCachedTeacher(): ?Teacher
    {
        return Cache::remember(
            'teacher_auth_' . Auth::id(),
            1800, // Increased from 300 to 1800 (30 minutes)
            function() {
                $user = User::whereId(Auth::id())->first();
                return $user ? Teacher::whereEmail($user->email)->first() : null;
            }
        );
    }

   protected static function getCachedTeacherWithArm(): ?Teacher
    {
        return Cache::remember(
            'teacher_with_arm_' . Auth::id(),
            1800, // Increased from 300 to 1800 (30 minutes)
            function() {
                $user = User::whereId(Auth::id())->first();
                return $user ? Teacher::with('arm')->whereEmail($user->email)->first() : null;
            }
        );
    }
}
