<?php

namespace App\Filament\Teacher\Resources\AssignmentResource\Pages;

use App\Filament\Teacher\Resources\AssignmentResource;
use App\Models\Teacher;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class EditAssignment extends EditRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_submissions')
                ->label('View Submissions')
                ->icon('heroicon-m-clipboard-document-check')
                ->color('info')
                ->url(fn (): string => static::getResource()::getUrl('view', ['record' => $this->record])),

            Actions\Action::make('duplicate')
                ->label('Duplicate Assignment')
                ->icon('heroicon-m-document-duplicate')
                ->color('gray')
                ->action(function () {
                    $newAssignment = $this->record->replicate();
                    $newAssignment->title = $this->record->title . ' (Copy)';
                    $newAssignment->status = 'draft';
                    $newAssignment->deadline = now()->addDays(7);
                    $newAssignment->save();

                    Notification::make()
                        ->title('Assignment Duplicated')
                        ->body('Assignment has been duplicated successfully.')
                        ->success()
                        ->send();

                    return redirect(static::getResource()::getUrl('edit', ['record' => $newAssignment]));
                }),

            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Assignment')
                ->modalDescription('Are you sure you want to delete this assignment? This will also delete all student submissions.')
                ->modalSubmitActionLabel('Yes, delete it'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        // Clear teacher cache
        AssignmentResource::clearTeacherCache();

        Notification::make()
            ->title('Assignment Updated!')
            ->body('Your changes have been saved successfully.')
            ->success()
            ->send();

        return $record;
    }

    protected function authorizeAccess(): void
    {
        $user = Auth::user();
        $teacher = Teacher::where('email', $user->email)->first();

        if (!$teacher || $this->record->teacher_id !== $teacher->id) {
            abort(403, 'You can only edit your own assignments.');
        }
    }

    public function getTitle(): string
    {
        return "Edit Assignment: {$this->record->title}";
    }

    public function getSubheading(): ?string
    {
        $stats = $this->record->getSubmissionStats();

        return "Submissions: {$stats['submitted']} | Class: {$this->record->class?->name} | Subject: {$this->record->subject?->code}";
    }
}
