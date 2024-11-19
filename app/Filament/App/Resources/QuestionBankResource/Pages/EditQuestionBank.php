<?php

namespace App\Filament\App\Resources\QuestionBankResource\Pages;

use App\Filament\App\Resources\QuestionBankResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuestionBank extends EditRecord
{
    protected static string $resource = QuestionBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
