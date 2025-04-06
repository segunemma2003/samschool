<?php

namespace App\Filament\App\Resources\StudentResource\Pages;

use App\Filament\App\Resources\StudentResource;
use Filament\Resources\Pages\Page;

class AdminStudentResults extends Page
{
    protected static string $resource = StudentResource::class;

    protected static string $view = 'filament.app.resources.student-resource.pages.admin-student-results';
    public $record;
    public function mount($record){
        $this->record = $record;
        // dd( $this->quizScoreId);
    }

    public static function generateRoute($record): string
    {
        return static::getResource()::getUrl('view-student-admin-result-details', ['record' => $record]);
    }
}
