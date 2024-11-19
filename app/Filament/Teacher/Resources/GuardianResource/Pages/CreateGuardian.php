<?php

namespace App\Filament\Teacher\Resources\GuardianResource\Pages;

use App\Filament\Teacher\Resources\GuardianResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CreateGuardian extends CreateRecord
{
    protected static string $resource = GuardianResource::class;


    public function afterCreate()
    {
        $data = $this->getRecord();
        Log::info($data);

        $user = User::updateOrCreate([
            "name"=> $data['name'],
            "email"=> $data['email'],
            "password"=>Hash::make($data["password"]),
            "username"=>$data["username"],
             "user_type"=>"parent"
        ]);

        // $this->getRecord()->update([
        //     "user_id"=>$user->id
        // ]);

    }
}
