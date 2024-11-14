<?php

namespace App\Filament\Teacher\Resources\QuestionBankResource\Pages;

use App\Filament\Teacher\Resources\QuestionBankResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQuestionBank extends EditRecord
{
    protected static string $resource = QuestionBankResource::class;
    // protected static string $view = 'filament.teacher.pages.edit-question';



    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // public function saveRecord()
    // {
    //     $this->record->save();
    //     session()->flash('message', 'Question updated successfully.');
    // }
}
