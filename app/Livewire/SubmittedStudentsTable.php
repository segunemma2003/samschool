<?php

namespace App\Livewire;

use App\Models\Assignment;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class SubmittedStudentsTable extends Component implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    public Assignment $assignment;


    // Pass assignment into the component dynamically
    public function mount(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }


    public function table(Table $table): Table
    {
        return $table
            ->query($this->assignment->answeredStudents()->query())
            ->columns([
                    TextColumn::make('student.name')->label('Student Name'),
                    TextColumn::make('student.pivot.total_score')->label('Total Score'),
                    TextColumn::make('student.pivot.comments_score')->label('Comments'),
                    TextColumn::make('student.pivot.updated_at')->label('Submission Time')->dateTime(),

            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }

    public function render()
    {
        return view('livewire.submitted-students-table');
    }
}
