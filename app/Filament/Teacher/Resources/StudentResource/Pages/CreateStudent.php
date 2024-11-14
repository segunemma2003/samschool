<?php

namespace App\Filament\Teacher\Resources\StudentResource\Pages;

use App\Filament\Teacher\Resources\StudentResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    public function afterCreate()
    {
        $data = $this->getRecord();
        Log::info($data);
        $mindex = User::max('id')+1;
        Log::info($mindex);
        $email = str_replace(' ', '.', $data['name']). $mindex.'@'.request()->getHost();
        $username = str_replace(' ', '.', $data['name']). $mindex;
        $user = User::updateOrCreate([
            "name"=> $data['name'],
            "email"=>  $email,
            "password"=>Hash::make($data["password"]),
            "username"=> $username,
            "user_type"=>"student"
        ]);



        $this->getRecord()->update([
            "email"=> $email,
            "username"=> $username
        ]);

    }
}
