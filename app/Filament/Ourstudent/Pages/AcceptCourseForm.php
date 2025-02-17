<?php

namespace App\Filament\Ourstudent\Pages;

use Filament\Pages\Page;

class AcceptCourseForm extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.ourstudent.pages.accept-course-form';
}
