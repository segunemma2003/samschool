<?php

namespace App\Filament\App\Resources\StudentResource\Pages;

use App\Filament\App\Resources\StudentResource;
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

        $user = User::create([
            "name"=> $data['name'],
            "email"=> $data['email'],
            "password"=>Hash::make($data["password"]),
            "username"=>$data["username"]
        ]);



        $this->getRecord()->update([
            "email"=>$data['name'] = str_replace(' ', '.', $data['name']).$user->id.'@'.request()->getHost()
        ]);

    }
}
