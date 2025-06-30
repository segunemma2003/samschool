<?php

namespace App\Filament\Teacher\Resources\CommunicationBookResource\Pages;

use App\Filament\Teacher\Resources\CommunicationBookResource;
use App\Models\Teacher;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;

class CreateCommunicationBook extends CreateRecord
{
    protected static string $resource = CommunicationBookResource::class;

    protected static ?string $title = '📝 Create New Communication Book';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cancel')
                ->label('Cancel')
                ->color('gray')
                ->icon('heroicon-m-x-mark')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Cache teacher lookup for better performance
        $cacheKey = 'teacher_' . Auth::id();
        $teacher = Cache::remember($cacheKey, 300, function () {
            $user = User::find(Auth::id());
            return $user ? Teacher::where('email', $user->email)->first() : null;
        });

        if ($teacher) {
            $data['teacher_id'] = $teacher->id;

            // Clear related caches when creating new communication book
            Cache::forget('communication_books_count_' . $teacher->id);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('✅ Communication Book Created!')
            ->body('The communication book entry has been successfully created.')
            ->duration(5000)
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->button()
                    ->url($this->getResource()::getUrl('view', ['record' => $this->record]))
                    ->label('View Entry'),
                \Filament\Notifications\Actions\Action::make('create_another')
                    ->button()
                    ->url($this->getResource()::getUrl('create'))
                    ->label('Create Another'),
            ]);
    }

    protected function afterCreate(): void
    {
        // Clear any relevant caches after creating
        $user = Auth::user();
        if ($user) {
            Cache::forget('teacher_' . $user->id);
        }
        $teacher = Teacher::where('email', $user->email)->first();
        if ($teacher) {
            Cache::forget('communication_books_count_' . $teacher->id);
            Cache::forget('students_' . $teacher->id . '_' . $teacher->arm->id ?? '');
        }
    }

    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl() => $this->getResource()::getBreadcrumb(),
            '📝 Create New Entry',
        ];
    }
}
