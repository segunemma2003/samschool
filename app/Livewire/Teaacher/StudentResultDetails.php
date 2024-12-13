<?php

namespace App\Livewire\Teaacher;

use App\Models\CourseForm;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Livewire\Component;

class StudentResultDetails extends Component  implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $record;
    public $academic;
    public $term;

    protected $listeners = ['refreshStudentResultDetails' => '$refresh'];

    public function mount($record, $academic, $term)
    {
        $this->record = $record;
        $this->academic = $academic;
        $this->term = $term;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query( CourseForm::query()
            ->where('student_id', $this->record->id)
            ->where('academic_year_id', $this->academicYearId)
            ->where('term_id', $this->termId))
            ->columns([
                TextColumn::make('name'),
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
        return view('livewire.teaacher.student-result-details');
    }
}
