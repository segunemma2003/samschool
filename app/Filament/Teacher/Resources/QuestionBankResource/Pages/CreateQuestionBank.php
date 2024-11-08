<?php

namespace App\Filament\Teacher\Resources\QuestionBankResource\Pages;

use App\Filament\Teacher\Resources\QuestionBankResource;
use App\Models\QuestionBank;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestionBank extends CreateRecord
{
    protected static string $resource = QuestionBankResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
{
    $examId = $data['exam_id'];
    $ndata = [];

    if (count($data['questions']) > 0) {
        // If more than one question exists, process the first one separately
        if (count($data['questions']) > 1) {
            // Skip the first question and process it later
            $firstQuestionData = $data['questions'][0];
            // Process the first question data
            $ndata = $this->processQuestion($firstQuestionData, $examId);

            // Now loop through the remaining questions
            $remainingQuestions = array_slice($data['questions'], 1);
            foreach ($remainingQuestions as $questionData) {

                // Process remaining questions
                $ndat = $this->processQuestion($questionData, $examId);
                QuestionBank::create($ndat);
            }
        } else {
            // Only one question, process it directly
            $ndata = $this->processQuestion($data['questions'][0], $examId);
        }
    }
    // dd($ndata);
    return $ndata;
}

protected function processQuestion(array $questionData, int $examId): array
{
    $questionBankData = [
        'exam_id' => $examId,
        'question' => $questionData['question'],
        'question_type' => $questionData['type'],
        'answer' => $questionData['answer'] ?? null,
        'marks' => $questionData['mark'],
        'options'=>json_encode($questionData['options']??[]),
        'hint' => $questionData['hint'] ?? null,
        'image' => $questionData['image'] ?? null,
    ];

    if ($questionData['type'] === 'multiple_choice') {
        $questionBankData['options'] = json_encode($questionData['options']);
    }

    return $questionBankData;
}
}
