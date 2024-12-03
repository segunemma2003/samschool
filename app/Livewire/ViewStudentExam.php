<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\QuizScore;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class ViewStudentExam extends Component implements HasTable,HasForms
// implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $record;

    public $exam;

    public function mount($record){
        $this->record = $record;


        $exam = Exam::with('studentsWithScores') // Eager load QuizScore with Student
        ->whereId($record->id)->first();

        if (!$exam) {
            return;
            Log::info('Exam not found or invalid tenant context');
        }

            // Access quiz scores and associated student details
            $quizScores = $exam->studentsWithScores;

            // Debugging output
            // dd($quizScores->toArray());
    }


    public function table(Table $table): Table
    {
        return $table
            ->query(
                QuizScore::where('exam_id', $this->record->id)
                    ->with('student')
            )
            ->columns([
                TextColumn::make('student.name')
                    ->label('Student Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('student.username')
                    ->label('Username')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('total_score')
                    ->label('Total Score')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    IconColumn::make('approved')
                    ->color(fn (string $state): string => match ($state) {
                        'yes'=>'success',
                        'no'=>'danger'
                    })->icon(fn (string $state): string => match ($state) {
                        'yes' => 'heroicon-s-check',  // Display a check icon if 'yes'
                        'no'  => 'heroicon-s-x-mark', // Display a cross icon if 'no'
                    }),
            ])
            ->actions([
                // View details action
                \Filament\Tables\Actions\Action::make('view')
    ->label('View Details')
    ->url(fn ($record) => \App\Filament\Teacher\Resources\ExamResource\Pages\ExamStudentDetails::generateRoute($record->id))
            ])
            ->bulkActions([
                // Bulk approve action
                \Filament\Tables\Actions\BulkAction::make('bulk_approve')
                ->label('Approve Scores')
                ->action(function ($records) {
                    // dd($records);
                    $ids = $records->pluck('id');
                    $updated =    QuizScore::whereIn('id', $ids )->update(['approved' => 'yes']);
                    if ($updated) {
                        \Filament\Notifications\Notification::make()
                            ->title('Selected scores approved.')
                            ->success()
                            ->send();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title('Failed to approve scores.')
                            ->danger()
                            ->send();
                    }
                }) ->requiresConfirmation() // Adds confirmation dialog
                ->modalHeading('Confirm Disapproval')
                ->modalDescription('Are you sure you want to disapprove the selected scores? This action cannot be undone.'),
                // ->modalSubmitActionLabel('Yes, Disapprove')
                // ->cancelButtonText('Cancel'),,
                // Bulk disapprove action
                \Filament\Tables\Actions\BulkAction::make('bulk_disapprove')
                    ->label('Disapprove Scores')
                    ->action(function ($records) {
                        // dd($records);
                        $ids = $records->pluck('id');
                        $updated =   QuizScore::whereIn('id', $ids )->update(['approved' => 'no']);
                        if ($updated) {
                            \Filament\Notifications\Notification::make()
                                ->title('Selected scores disapproved.')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Failed to disapprove scores.')
                                ->danger()
                                ->send();
                        }
                    }) ->requiresConfirmation() // Adds confirmation dialog
                    ->modalHeading('Confirm Disapproval')
                    ->modalDescription('Are you sure you want to disapprove the selected scores? This action cannot be undone.')
                    // ->modalSubmitActionLabel('Yes, Disapprove')
                    // ->cancelButtonText('Cancel'),,
            ])->defaultSort('created_at', 'desc');
    }

    public function render(): View
    {
        return view('livewire.view-student-exam');
    }
}
