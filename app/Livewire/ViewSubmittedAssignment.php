<?php

namespace App\Livewire;

use App\Models\Assignment;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Livewire\Component;

class ViewSubmittedAssignment extends Component implements HasForms, HasInfolists
{

    use InteractsWithForms;
    use InteractsWithInfolists;

    public $assignment;

    public function mount(Assignment $assignment): void
    {
        $this->assignment = $assignment;
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required(),
                MarkdownEditor::make('content'),

            ]);

    }


    public function assignmentInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->assignment)
            ->schema([
                TextEntry::make('title'),
                TextEntry::make('description'),
                TextEntry::make('deadline')->dateTime()

            ]);
    }

    public function create(): void
    {
        dd($this->form->getState());
    }

    public function render()
    {
        return view('livewire.view-submitted-assignment');
    }
}
