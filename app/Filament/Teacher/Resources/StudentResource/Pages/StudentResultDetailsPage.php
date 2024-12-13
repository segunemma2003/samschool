<?php

namespace App\Filament\Teacher\Resources\StudentResource\Pages;

use App\Filament\Teacher\Resources\StudentResource;
use Filament\Resources\Pages\Page;

class StudentResultDetailsPage extends Page
{
    protected static string $resource = StudentResource::class;

    protected static string $view = 'filament.teacher.resources.student-resource.pages.student-result-details-page';

    public $record;
    public function mount($record){
        $this->record = $record;
        // dd( $this->quizScoreId);
    }

    public static function generateRoute($record): string
    {
        return static::getResource()::getUrl('view-student-result-details', ['record' => $record]);
    }
}
