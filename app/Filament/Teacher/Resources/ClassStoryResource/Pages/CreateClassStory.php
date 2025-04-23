<?php

namespace App\Filament\Teacher\Resources\ClassStoryResource\Pages;

use App\Filament\Teacher\Resources\ClassStoryResource;
use App\Models\Teacher;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateClassStory extends CreateRecord
{
    protected static string $resource = ClassStoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user= User::where('id', Auth::id())->first();
        $teacher = Teacher::with('arm')->whereEmail($user->email)->first();
        $data['teacher_id'] = $teacher->id;
        if ($teacher && $teacher->arm) {
            $arm = $teacher->arm;
            $data['class_id'] = $arm->class_id;
            $data['arm_id'] = $arm->arm_id;
        }
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
