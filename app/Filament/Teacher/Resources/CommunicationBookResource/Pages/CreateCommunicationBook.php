<?php

namespace App\Filament\Teacher\Resources\CommunicationBookResource\Pages;

use App\Filament\Teacher\Resources\CommunicationBookResource;
use App\Models\Teacher;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCommunicationBook extends CreateRecord
{
    protected static string $resource = CommunicationBookResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user= User::where('id', Auth::id())->first();
        $teacher = Teacher::whereEmail($user->email)->first();
        $data['teacher_id'] = $teacher->id;
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
