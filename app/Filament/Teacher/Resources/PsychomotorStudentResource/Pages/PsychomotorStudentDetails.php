<?php

namespace App\Filament\Teacher\Resources\PsychomotorStudentResource\Pages;

use App\Filament\Teacher\Resources\PsychomotorStudentResource;
use Filament\Resources\Pages\Page;

class PsychomotorStudentDetails extends Page
{
    protected static string $resource = PsychomotorStudentResource::class;

    protected static string $view = 'filament.teacher.resources.psychomotor-student-resource.pages.psychomotor-student-details';

    public $record;
    public function mount($record){
        $this->record = $record;
        // dd( $this->quizScoreId);
    }

    public static function generateRoute($record): string
    {
        return static::getResource()::getUrl('view-student-psych', ['record' => $record]);
    }
}
