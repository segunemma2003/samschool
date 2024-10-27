<?php

namespace App\Filament\Teacher\Resources\LectureResource\Pages;

use App\Filament\Teacher\Resources\LectureResource;
use App\Models\Teacher;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLecture extends CreateRecord
{
    protected static string $resource = LectureResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
         $id = Auth::id();
         $user = User::whereId($id)->first();
         $teacher = Teacher::whereEmail($user->email)->first();
         $data['teacher_id']= $teacher->id;
        return $data;
    }

}
