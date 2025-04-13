<?php

namespace App\Livewire;

use App\Filament\Teacher\Resources\AssignmentResource\Pages\ViewSubmittedAssignment;
use App\Filament\Teacher\Resources\AssignmentResource\Pages\ViewSubmittedAssignmentTeacher;
use App\Models\Assignment;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Filament\Tables;

class SubmittedStudentsTable extends Component implements HasTable, HasForms, HasActions
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;
    public Assignment $assignment;

    protected static ?string $panel = 'teacher';


    // Pass assignment into the component dynamically
    public function mount(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }


    public function table(Table $table): Table
    {
        // dd( $this->assignment->answeredStudents()->getQuery()->get());
        return $table
            ->query(
                $this->assignment->answeredStudents()->getQuery())
            ->columns([
                    TextColumn::make('No')->rowIndex(),
                    TextColumn::make('name')->label('Student Name')
                    ->searchable()
                    ->sortable(),
                    TextColumn::make('total_score')->label('Total Score')
                    ->sortable(),
                    TextColumn::make('comments_score')->label('Comments'),
                    TextColumn::make('updated_at')->label('Submission Time')->dateTime(),
                    TextColumn::make('comments_score')
                    ->label('Status')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->comments_score==null ? 'Not Marked': "Marked"; // Show "Not Marked" if null
                    }),

            ])
            ->filters([
                // ...
            ])
            ->actions([

                // ViewAction::make(),
                    Tables\Actions\Action::make('view')->url(function($record) {

                    return route('filament.teacher.pages.view-submitted-assignment-teacher', [
                    'assignment' => $this->assignment,
                    'student' => $record->student_id,
                ]);

    })

            ])
            ->bulkActions([
                // ...
            ]);
    }


    public static function getPages(): array
    {
        return [
            'view'=> ViewSubmittedAssignment::route('/{record}')
        ];
    }



    public function render()
    {
        return view('livewire.submitted-students-table');
    }
}
