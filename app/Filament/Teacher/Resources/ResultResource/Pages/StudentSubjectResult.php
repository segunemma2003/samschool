<?php

namespace App\Filament\Teacher\Resources\ResultResource\Pages;

use App\Filament\Teacher\Resources\ResultResource;
use Filament\Resources\Pages\Page;

class StudentSubjectResult extends Page
{
    protected static string $resource = ResultResource::class;

    protected static string $view = 'filament.teacher.resources.result-resource.pages.student-subject-result';

    public $record;
    public function mount($record){
        $this->record = $record;
        // dd( $this->quizScoreId);
    }

    public static function generateRoute($record): string
    {
        return static::getResource()::getUrl('view-student-results', ['record' => $record]);
    }
}
