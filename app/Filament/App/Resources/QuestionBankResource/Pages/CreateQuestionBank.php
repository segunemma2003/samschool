<?php

namespace App\Filament\App\Resources\QuestionBankResource\Pages;

use App\Filament\App\Resources\QuestionBankResource;
use App\Models\QuestionBank;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class CreateQuestionBank extends CreateRecord
{
    protected static string $resource = QuestionBankResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {
            DB::beginTransaction();

            $examId = $data['exam_id'];
            $questions = $data['questions'] ?? [];

            if (empty($questions)) {
                throw new \Exception('No questions provided');
            }

            // Process the first question (will be returned for the main record)
            $firstQuestion = array_shift($questions);
            $mainQuestionData = $this->processQuestion($firstQuestion, $examId);

            // Process remaining questions individually using Eloquent
            if (!empty($questions)) {
                $this->createQuestionsIndividually($questions, $examId);
            }

            // Clear relevant caches
            $this->clearRelatedCaches($examId);

            DB::commit();

            // Show success notification
            Notification::make()
                ->title('Questions Created Successfully')
                ->success()
                ->body(sprintf('Created %d question(s) for the selected exam.', count($data['questions'])))
                ->send();

            return $mainQuestionData;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating questions: ' . $e->getMessage(), [
                'exam_id' => $examId ?? null,
                'questions_count' => count($data['questions'] ?? []),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Error Creating Questions')
                ->danger()
                ->body('An error occurred while creating the questions. Please try again.')
                ->send();

            throw $e;
        }
    }

    private function createQuestionsIndividually(array $questions, int $examId): void
    {
        foreach ($questions as $questionData) {
            $processedData = $this->processQuestion($questionData, $examId);
            QuestionBank::create($processedData);
        }
    }

    private function processQuestion(array $questionData, int $examId): array
    {
        $questionBankData = [
            'exam_id' => $examId,
            'question' => $this->sanitizeHtml($questionData['question']),
            'question_type' => $questionData['question_type'],
            'answer' => $questionData['answer'] ?? null,
            'marks' => $questionData['mark'] ?? 1,
            'options' => $this->processOptions($questionData),
            'hint' => $questionData['hint'] ?? null,
            'image' => $questionData['image'] ?? null,
        ];

        // Auto-generate answer for multiple choice and true/false questions
        if (in_array($questionData['question_type'], ['multiple_choice', 'true_false'])) {
            $questionBankData['answer'] = $this->extractCorrectAnswer($questionData);
        }

        return $questionBankData;
    }

    private function processOptions(array $questionData): array
    {
        $options = $questionData['options'] ?? [];

        if (!is_array($options)) {
            return [];
        }

        // Clean and validate options
        $processedOptions = [];
        $hasCorrectAnswer = false;

        foreach ($options as $option) {
            if (!empty($option['option'])) {
                $processedOption = [
                    'option' => $this->sanitizeHtml($option['option']),
                    'is_correct' => (bool) ($option['is_correct'] ?? false),
                    'image' => $option['image'] ?? null,
                ];

                if ($processedOption['is_correct']) {
                    $hasCorrectAnswer = true;
                }

                $processedOptions[] = $processedOption;
            }
        }

        // Validate that at least one answer is marked as correct for non-open-ended questions
        if (in_array($questionData['question_type'], ['multiple_choice', 'true_false']) && !$hasCorrectAnswer) {
            Log::warning('No correct answer marked for question', [
                'question_type' => $questionData['question_type'],
                'question' => substr($questionData['question'], 0, 100) . '...'
            ]);
        }

        return $processedOptions;
    }

    private function extractCorrectAnswer(array $questionData): ?string
    {
        $options = $questionData['options'] ?? [];

        foreach ($options as $option) {
            if (!empty($option['is_correct']) && $option['is_correct'] === true) {
                return $option['option'];
            }
        }

        return null;
    }

    private function sanitizeHtml(string $content): string
    {
        // Basic HTML sanitization - adjust based on your needs
        return strip_tags($content, '<p><br><strong><em><u><ol><ul><li>');
    }

    private function clearRelatedCaches(int $examId): void
    {
        try {
            // Clear admin-specific caches
            Cache::forget("admin_questions_count_all");
            Cache::forget("admin_exam_options_all");

            // Clear exam-specific caches
            Cache::forget("exam_{$examId}_questions_count");

            // Clear general academic data cache
            Cache::forget('current_academic_data');

        } catch (\Exception $e) {
            Log::warning('Error clearing caches: ' . $e->getMessage());
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Questions created successfully';
    }

    protected function afterCreate(): void
    {
        // Log the creation for audit purposes
        Log::info('Question bank created by admin', [
            'user_id' => auth()->id(),
            'exam_id' => $this->record->exam_id,
            'question_count' => 1, // This is just the main record
        ]);
    }
}
