<?php

namespace App\Filament\Ourstudent\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Page;

class ExamReviewPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.ourstudent.pages.exam-review-page';

    public function mount()
    {
        Filament::getPanel()->navigation(false);
    }
}
