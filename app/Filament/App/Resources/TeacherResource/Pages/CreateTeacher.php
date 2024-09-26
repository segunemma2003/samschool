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
        $data = $this->getRecord();
        Log::info($data);

        $user = User::create([
            "name"=> $data['name'],
            "email"=> $data['email'],
            "password"=>Hash::make($data["password"]),
            "username"=>$data["username"]
        ]);

        // $this->getRecord()->update([
        //     "user_id"=>$user->id
        // ]);

    }
}
