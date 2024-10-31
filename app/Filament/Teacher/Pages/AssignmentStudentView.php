<?php

namespace App\Filament\Teacher\Pages;

use Filament\Pages\Page;

class AssignmentStudentView extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.teacher.pages.assignment-student-view';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


}
