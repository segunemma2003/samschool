<?php

namespace App\Filament\App\Resources\ExamCommentResource\Pages;

use App\Filament\App\Resources\ExamCommentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExamComment extends EditRecord
{
    protected static string $resource = ExamCommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
