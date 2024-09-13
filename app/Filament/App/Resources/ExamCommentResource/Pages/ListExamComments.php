<?php

namespace App\Filament\App\Resources\ExamCommentResource\Pages;

use App\Filament\App\Resources\ExamCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExamComments extends ListRecords
{
    protected static string $resource = ExamCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
