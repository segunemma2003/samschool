<?php

namespace App\Livewire;

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
                ViewAction::make('view')->url(fn($record) => route('filament.pages.assignment-student-view', [
                    'assignment' => $this->assignment,
                    'student' => $record,
                ])),
            ])
            ->bulkActions([
                // ...
            ]);
    }



    // public function viewAction(): Action
    // {
    //     return Action::make('view')
    //         ->requiresConfirmation()
    //         ->action(fn () => $this->post->delete());
    // }


    public function assignmentInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->assignment)
            ->schema([
                Grid::make(2)
    ->schema([
        TextEntry::make('title'),

                TextEntry::make('deadline')->dateTime(),
                TextEntry::make('file')
                ->label('Download File')
                ->formatStateUsing(function ($state) {
                    if ($state) {
                        // Generate the download URL
                        $fileUrl = Storage::disk('cloudinary')->url($state);

                        // Return HTML for download icon and link
                        return sprintf(
                            '<a href="%s" target="_blank" download class="flex items-center space-x-1 text-blue-500 hover:underline">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v4h16v-4m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                <span>Download File</span>
                            </a>',
                            $fileUrl
                        );
                    }
                    return 'No file available';
                })
                ->html(),


            ]),
    TextEntry::make('description')->html(),



            ]);
    }

    // public static function getPages(): array
    // {
    //     return [
    //         'view' => ViewSubmittedAssignmentTeacher::route('/{record}'),
    //     ];
    // }

    public function render()
    {
        return view('livewire.submitted-students-table');
    }
}
