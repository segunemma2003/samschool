<?php

namespace App\Filament\Teacher\Resources\ExamResource\Pages;

use App\Filament\Teacher\Resources\ExamResource;
use Filament\Actions;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\Page;

class ViewExamStudent extends Page
{
    protected static string $resource = ExamResource::class;

    protected static string $view = "filament.teacher.resources.pages.exam-students";

    use InteractsWithRecord;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

}

