<?php

namespace App\Filament\Ourstudent\Pages;

use Filament\Pages\Page;
use App\Models\QuestionBank;
use Filament\Facades\Filament;
use Livewire\WithPagination;

class ExamPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.ourstudent.pages.exam-page';




    use WithPagination;

    protected static ?string $navigationLabel = null;
    protected static ?string $title = 'Exam';

    public function mount()
    {
        Filament::getPanel()->navigation(false);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


}
