<?php

namespace App\Filament\App\Resources\TeacherResource\Pages;

use App\Filament\App\Resources\TeacherResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    public function afterCreate()
    {
        $teacher = $this->getRecord();
        // Log::info($teacher);

        $user = User::firstOrCreate(
            ['email' => $teacher['email']], // Match condition
            [
                'name' => $teacher['name'],
                'password' => Hash::make($teacher['password']),
                'username' => $teacher['username'],
                'user_type' => 'teacher',
            ]
        );

        // $this->getRecord()->update([
        //     "user_id"=>$user->id
        // ]);

    }
}
