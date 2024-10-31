<?php

namespace App\Filament\Teacher\Pages;

use Filament\Pages\Page;

class SubmittedStudents extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.teacher.pages.submitted-students';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

}
