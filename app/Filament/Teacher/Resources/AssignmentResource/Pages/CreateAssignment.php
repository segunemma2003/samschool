<?php

namespace App\Filament\Teacher\Resources\AssignmentResource\Pages;

use App\Filament\Teacher\Resources\AssignmentResource;
use App\Models\Teacher;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreateAssignment extends CreateRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        $teacher = Teacher::where('email', $user->email)->first();

        if ($teacher) {
            $data['teacher_id'] = $teacher->id;
        }

        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'available';
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = static::getModel()::create($data);

        // Clear teacher cache
        AssignmentResource::clearTeacherCache();

        Notification::make()
            ->title('Assignment Created Successfully!')
            ->body('Your assignment has been created and is now available to students.')
            ->success()
            ->duration(5000)
            ->send();

        return $record;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Assignment created successfully!';
    }

    public function getTitle(): string
    {
        return 'Create New Assignment';
    }
}
