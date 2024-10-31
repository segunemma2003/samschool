<?php

namespace App\Filament\Ourstudent\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Page;

class ExamInstructions extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.ourstudent.pages.exam-instructions';
    protected static ?string $navigationLabel = null;

    public function mount()
    {
        Filament::getPanel()->navigation(false);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

}
