<?php

namespace App\Livewire;

use App\Models\Assignment;
use App\Models\Student;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
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

class ViewAssignment extends Component implements HasForms, HasInfolists
{

    use InteractsWithForms;
    use InteractsWithInfolists;

    public $assignment;

    public ?array $data = [];
    public function mount($assignment): void
    {
        // dd($assignment);
        $this->assignment = $assignment;
        // $this->form->fill();

        $user = Auth::user();
        $student = Student::whereEmail($user->email)->first();

        // Fetch existing pivot data if it exists
        $pivotData = $this->assignment->students()->where('student_id', $student->id)->first();

        // Pre-fill the form if pivot data is found
        if ($pivotData) {
            $this->data = [
                'answer' => $pivotData->pivot->answer,
                'status' => $pivotData->pivot->status,
                'file' => $pivotData->pivot->file, // Optional: Only if file is needed
            ];
        }

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
               RichEditor::make('answer')
               ->required()
               ->label("Answer"),
               Select::make('status')
               ->options([
                "draft"=>"Draft",
                "submitted"=>"Submit"
               ]),
               FileUpload::make('file')
               ->disk('cloudinary')

            ]) ->statePath('data');

    }


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

    public function create(): void
    {
        $result = $this->form->getState();
        $user = User::whereId(Auth::id())->first();
        $student = Student::whereEmail($user->email)->first();

        $this->assignment->students()->syncWithoutDetaching([
            $student->id => [
                'status' => $result['status'],
                'answer' => $result['answer'],
                'teacher_id'=>$this->assignment->teacher_id,
                'file'=> $result['file']
            ]
            ]);
            $message = $result['status'] === "draft" ? 'Draft Saved successfully' : 'Assignment Submitted successfully';
            Notification::make()
                ->title($message)
                ->success()
                ->send();


    }

    public function render()
    {
        return view('livewire.view-assignment');
    }
}
