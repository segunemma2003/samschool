<?php

namespace App\Filament\App\Resources\ExamResource\Pages;

use App\Filament\App\Resources\ExamResource;
use App\Models\QuizScore;
use App\Models\QuizSubmission;
use Filament\Resources\Pages\Page;

class ExamStudentDetails extends Page
{
    protected static string $resource = ExamResource::class;
    protected static string $view = 'filament.app.resources.exam-resource.pages.exam-student-details';

    public $quizScoreId;
    public $quizScore;
    public $studentDetails;
    public $questions;

    public function mount($quizScoreId): void
    {
        $this->quizScoreId = $quizScoreId;

        $this->quizScore = QuizScore::with([
            'exam.subject.teacher',
            'exam.subject.subjectDepot',
            'student'
        ])->findOrFail($quizScoreId);

        $this->studentDetails = $this->quizScore->student;

        $this->questions = QuizSubmission::where('quiz_score_id', $quizScoreId)
            ->with(['question'])
            ->get();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('back')
                ->label('Back to Students')
                ->icon('heroicon-o-arrow-left')
                ->url($this->getResource()::getUrl('view-students', ['record' => $this->quizScore->exam_id]))
                ->color('gray'),
        ];
    }

    public function getTitle(): string
    {
        return "Exam Details - {$this->studentDetails->name}";
    }

    public static function generateRoute(int $quizScoreId): string
    {
        return static::getResource()::getUrl('exam-student-details', ['quizScoreId' => $quizScoreId]);
    }
}
