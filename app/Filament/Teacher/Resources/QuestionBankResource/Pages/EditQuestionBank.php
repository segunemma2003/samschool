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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure options is an array
        if (isset($data['options']) && is_string($data['options'])) {
            $data['options'] = json_decode($data['options'], true) ?? [];
        }

        // Automatically update the answer column if question type is multiple choice or true/false
        if (in_array($data['question_type'], ['multiple_choice', 'true_false']) && (empty($data['answer']) || is_null($data['answer']))) {
            foreach ($data['options'] as $option) {
                if (!empty($option['is_correct']) && $option['is_correct'] === true) {
                    $data['answer'] = $option['option'];
                    break; // Stop after the first correct answer
                }
            }
        }

        return $data;
    }
}
