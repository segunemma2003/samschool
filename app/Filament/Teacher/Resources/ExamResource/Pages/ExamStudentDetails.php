<?php

namespace App\Filament\Teacher\Resources\ExamResource\Pages;

use App\Filament\Teacher\Resources\ExamResource;
use Filament\Resources\Pages\Page;

class ExamStudentDetails extends Page
{
    protected static string $resource = ExamResource::class;

    protected static string $view = 'filament.teacher.resources.exam-resource.pages.exam-student-details';

    public $quizScoreId;
    public function mount($quizScoreId){
        $this->quizScoreId = $quizScoreId;
        // dd( $this->quizScoreId);
    }

    public static function generateRoute(int $quizScoreId): string
    {
        return static::getResource()::getUrl('exam-student-details', ['quizScoreId' => $quizScoreId]);
    }
}
