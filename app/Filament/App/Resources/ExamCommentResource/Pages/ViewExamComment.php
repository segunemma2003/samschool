<?php

namespace App\Filament\App\Resources\ExamCommentResource\Pages;

use App\Filament\App\Resources\ExamCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExamComment extends ViewRecord
{
    protected static string $resource = ExamCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
