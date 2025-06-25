<?php

namespace App\Traits;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait OptimizedTeacherLookup
{
    protected function getCurrentTeacher()
    {
        $userId = Auth::id();

        return cache()->remember("teacher_for_user_{$userId}", 300, function() use ($userId) {
            $user = User::whereId($userId)->first();
            return Teacher::where('email', $user->email)->first();
        });
    }
}
