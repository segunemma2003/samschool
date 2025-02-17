<?php

namespace App\Livewire\Result;

use App\Filament\Teacher\Resources\ResultResource\Pages\StudentSubjectResult;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class Students extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    // public $termId;
    // public $academic;
    // public $terms;
    // public $academicYears;

    public function mount()
    {
        // $this->terms = Term::all();
        // $this->academicYears = AcademicYear::all();
        // $this->termId = Term::query()->where('status', "true")->first()?->id; // Default to the first term
        // $this->academic = AcademicYear::query()->where('status', "true")->first()?->id;
    }

    public function table(Table $table): Table
    {
        $userId = Auth::id();
        $user = User::whereId($userId)->first();
        $teacher = Teacher::whereEmail($user->email)->first();
        return $table
        ->query(Subject::where('teacher_id', $teacher->id))
            ->columns([
                TextColumn::make('row_number')
                ->label('S/N') // Label for the serial number column
                ->rowIndex(),  // Automatically generates row numbers
            TextColumn::make('subjectDepot.name'),
            TextColumn::make('pass_mark'),
            TextColumn::make('final_mark'),
            TextColumn::make('class.name'),
            ])
            ->filters([
            //     SelectFilter::make('term_id')
            //     ->label('Term')
            //     ->options($this->terms->pluck('name', 'id')->toArray())
            //     ->default($this->termId)
            //     ->searchable(),

            // SelectFilter::make('academic_year_id')
            //     ->label('Academic Year')
            //     ->options($this->academicYears->pluck('title', 'id')->toArray())
            //     ->default($this->academic)
            //     ->searchable()
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('view')->label('View Details')
                ->url(fn ($record) => StudentSubjectResult::generateRoute($record->id))
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.result.students');
    }
}
