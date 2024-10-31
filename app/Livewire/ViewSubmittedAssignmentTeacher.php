<?php

namespace App\Livewire;

use App\Models\Assignment;
use App\Models\Student;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ViewSubmittedAssignmentTeacher extends Component implements HasForms, HasInfolists
{


    use InteractsWithForms;
    use InteractsWithInfolists;

    public $assignment;
    public $mrecord;
    public ?array $data = [];

    public function mount( $assignment,  $mrecord){

        $this->assignment= $assignment;
        $this->mrecord = $mrecord;
        $assignment = Assignment::whereId($this->assignment)->first();
        $pivotData = $assignment->students()->where('student_id', $mrecord)->first();

        if ($pivotData) {
            $this->data = [
                'score' => $pivotData->pivot->total_score,
                'comment_score' => $pivotData->pivot->comments_score,

            ];
        }
        $this->form->fill($this->data);

    }


    public function form(Form $form): Form
    {
        return $form
            ->schema([
               TextInput::make('score')
               ->numeric()
               ->required(),

               RichEditor::make('comment_score')->required()

            ])->statePath('data');

    }


    public function assignmentInfolist(Infolist $infolist): Infolist
{
    $studentId = $this->mrecord;
    $mrecord = Student::whereId($studentId)->first();
    $studentName = $mrecord->name;

    // Fetch the assignment record with the specific student and pivot data
    $assignment = Assignment::whereId($this->assignment)
        ->with(['subject','students' => function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        }])
        ->first();
$subject= $assignment->subject->name;
    $studentWithPivot = $assignment->students->first();

    return $infolist
        ->record($assignment)
        ->schema([
            Grid::make(2)
                ->schema([
                    TextEntry::make('student_name')
                        ->label('Student Name')
                        ->default($studentName)
                        ,
                    TextEntry::make('subject.name')->default($subject)
                    ->label('Subject'),
                    TextEntry::make('title'),

                    TextEntry::make('deadline')->dateTime(),

                    TextEntry::make('file')
                        ->label('Download File')
                        ->formatStateUsing(function ($state) {
                            if ($state) {
                                $fileUrl = Storage::disk('cloudinary')->url($state);
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

                    // Access the pivot field 'answer' for the specific student
                    TextEntry::make('students.0.pivot.answer')
                        ->default($studentWithPivot->pivot->answer ?? 'No answer provided')
                        ->html()->columnSpanFull()
                ]),
        ]);
}
    public function create()
    {
        $result = $this->form->getState();
        $assignment = Assignment::whereId($this->assignment)->first();
        $student = Student::whereId($this->mrecord)->first();

        $assignment->students()->syncWithoutDetaching([
            $student->id => [
                'total_score' => $result['score'],
                'comments_score' => $result['comment_score'],
            ]
            ]);
            $message = "Successfully added Scores";
            Notification::make()
                ->title($message)
                ->success()
                ->send();

            return redirect()->route('filament.teacher.resources.assignments.index');


    }

    public function render()
    {
        return view('livewire.view-submitted-assignment-teacher');
    }
}
