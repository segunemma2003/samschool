<?php

namespace App\Filament\App\Resources\ExamResource\Pages;

use App\Filament\App\Resources\ExamResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExams extends ListRecords
{
    protected static string $resource = ExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
