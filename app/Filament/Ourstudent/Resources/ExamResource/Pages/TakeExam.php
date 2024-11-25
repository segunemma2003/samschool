<?php

namespace App\Filament\Ourstudent\Resources\ExamResource\Pages;

use App\Filament\Ourstudent\Resources\ExamResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ViewRecord;

class TakeExam extends ViewRecord
{
    protected static string $resource = ExamResource::class;


    protected static string $view = 'filament.ourstudent.pages.exam-instructions';

    protected static ?string $navigationLabel = null;

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return false;
    }

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        if (! $this->hasInfolist()) {
            $this->fillForm();
        }

        Filament::getPanel()->navigation(false);
    }



}
