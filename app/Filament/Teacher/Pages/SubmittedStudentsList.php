<?php

namespace App\Filament\Teacher\Pages;

use App\Models\Assignment;
use Filament\Pages\Page;

class SubmittedStudentsList extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.teacher.pages.submitted-students-list';

    public Assignment $assignment;

    public function mount(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }

    protected function getTableQuery()
    {
        // Query the students who have 'submitted' status for this assignment
        return $this->assignment->students()
            ->wherePivot('status', 'submitted')
            ->getQuery(); // To ensure we get a builder instance
    }
}
