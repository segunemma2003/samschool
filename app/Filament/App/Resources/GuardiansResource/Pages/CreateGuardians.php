<?php

namespace App\Filament\App\Resources\GuardiansResource\Pages;

use App\Filament\App\Resources\GuardiansResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CreateGuardians extends CreateRecord
{
    protected static string $resource = GuardiansResource::class;



    public function afterCreate()
    {
        $data = $this->getRecord();
        // Log::info($data);
//
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
