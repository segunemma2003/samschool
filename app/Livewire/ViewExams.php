<?php

namespace App\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Livewire\Component;

class ViewExams  extends Component implements  HasForms, HasActions
{

    // use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;

    protected static ?string $panel = 'teacher';

    public $record;

    public function mount($record)
    {
        $this->record = $record;
    }


    public function render()
    {
        return view('livewire.view-exams');
    }
}
