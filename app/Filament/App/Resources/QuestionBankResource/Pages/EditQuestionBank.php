<?php

namespace App\Filament\App\Resources\QuestionBankResource\Pages;

use App\Filament\App\Resources\QuestionBankResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class EditQuestionBank extends EditRecord
{
    protected static string $resource = QuestionBankResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Question')
                ->modalDescription('Are you sure you want to delete this question? This action cannot be undone.')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Question deleted')
                        ->body('The question has been deleted successfully.')
                ),
            Actions\Action::make('duplicate')
                ->label('Duplicate Question')
                ->icon('heroicon-o-document-duplicate')
                ->color('gray')
                ->action(function () {
                    $this->duplicateQuestion();
                })
                ->requiresConfirmation()
                ->modalHeading('Duplicate Question')
                ->modalDescription('This will create a copy of this question that you can modify.')
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        try {
            // Sanitize question text
            if (isset($data['question'])) {
                $data['question'] = $this->sanitizeHtml($data['question']);
            }

            // Process options if they exist
            if (isset($data['options'])) {
                $data['options'] = $this->processOptions($data);
            }

            // Auto-update answer for multiple choice and true/false questions
            if (in_array($data['question_type'], ['multiple_choice', 'true_false'])) {
                $data['answer'] = $this->extractCorrectAnswer($data);
            }

            // Sanitize hint if provided
            if (isset($data['hint'])) {
                $data['hint'] = $this->sanitizeHtml($data['hint']);
            }

            // Validate marks
            if (isset($data['mark'])) {
                $data['marks'] = max(0.5, min(100, $data['mark'])); // Ensure marks are between 0.5 and 100
                unset($data['mark']); // Remove the old key
            }

            return $data;

        } catch (\Exception $e) {
            Log::error('Error processing question data: ' . $e->getMessage(), [
                'question_id' => $this->record->id,
                'data' => $data
            ]);

            Notification::make()
                ->title('Error Updating Question')
                ->danger()
                ->body('An error occurred while updating the question. Please try again.')
                ->send();

            throw $e;
        }
    }

    private function processOptions(array $data): array
    {
        $options = $data['options'] ?? [];

        if (!is_array($options)) {
            // Handle case where options might be a JSON string
            if (is_string($options)) {
                $options = json_decode($options, true) ?? [];
            } else {
                return [];
            }
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
        if (in_array($data['question_type'], ['multiple_choice', 'true_false']) && !$hasCorrectAnswer) {
            Log::warning('No correct answer marked for question during edit', [
                'question_id' => $this->record->id,
                'question_type' => $data['question_type']
            ]);
        }

        return $processedOptions;
    }

    private function extractCorrectAnswer(array $data): ?string
    {
        $options = $data['options'] ?? [];

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

    private function duplicateQuestion(): void
    {
        try {
            $originalQuestion = $this->record;

            $duplicatedData = $originalQuestion->toArray();
            unset($duplicatedData['id'], $duplicatedData['created_at'], $duplicatedData['updated_at']);

            // Add "Copy of" prefix to the question
            $duplicatedData['question'] = 'Copy of ' . $duplicatedData['question'];

            $newQuestion = $originalQuestion->replicate();
            $newQuestion->question = $duplicatedData['question'];
            $newQuestion->save();

            Notification::make()
                ->title('Question Duplicated')
                ->success()
                ->body('A copy of the question has been created successfully.')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->button()
                        ->url(static::getResource()::getUrl('edit', ['record' => $newQuestion->id]))
                        ->label('Edit Copy'),
                ])
                ->send();

            // Clear caches
            $this->clearRelatedCaches();

        } catch (\Exception $e) {
            Log::error('Error duplicating question: ' . $e->getMessage(), [
                'original_question_id' => $this->record->id
            ]);

            Notification::make()
                ->title('Error')
                ->danger()
                ->body('Failed to duplicate the question. Please try again.')
                ->send();
        }
    }

    private function clearRelatedCaches(): void
    {
        try {
            // Clear admin-specific caches
            Cache::forget("admin_questions_count_all");
            Cache::forget("admin_exam_options_all");

            // Clear exam-specific caches
            if ($this->record->exam_id) {
                Cache::forget("exam_{$this->record->exam_id}_questions_count");
            }

            // Clear general academic data cache
            Cache::forget('current_academic_data');

        } catch (\Exception $e) {
            Log::warning('Error clearing caches: ' . $e->getMessage());
        }
    }

    protected function afterSave(): void
    {
        // Clear caches after successful save
        $this->clearRelatedCaches();

        // Log the update for audit purposes
        Log::info('Question bank updated by admin', [
            'user_id' => auth()->id(),
            'question_id' => $this->record->id,
            'exam_id' => $this->record->exam_id,
        ]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Question updated successfully';
    }
}
