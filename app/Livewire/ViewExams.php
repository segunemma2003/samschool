<?php

namespace App\Livewire;

use App\Models\QuestionBank;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
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
    public $showEditModal = true;

    public $exam;
    public $questions = [];

    public $editing = [
        'id' => null,
        'question' => '',
        'question_type' => '',
        'options' => [],
        'answer' => '',
        'mark' => 1,
        'hint' => '',
        'image' => null,
    ];


    public function mount($record)
    {

        $this->record = $record;

        $this->exam = $record;

        $this->questions = $this->exam->questions->toArray();

    }


    public function editQuestion($questionId)
    {
        $question = QuestionBank::findOrFail($questionId);
        $this->editing = [
            'id' => $question->id,
            'question' => $question->question,
            'question_type' => $question->question_type,
            'options' => $question->options ?? [],
            'answer' => $question->answer,
            'mark' => $question->mark,
            'hint' => $question->hint,
            'image' => $question->image,
        ];
        $this->showEditModal = true;
    }

    public function addOption()
        {
            $this->editing['options'][] = ''; // Add a new empty option
        }

        public function removeOption($index)
        {
            unset($this->editing['options'][$index]);
            $this->editing['options'] = array_values($this->editing['options']); // Reindex the array
        }

    public function updateQuestion()
    {
        $this->validate([
            'editing.question' => 'required|string',
            'editing.question_type' => 'required|string',
            'editing.options' => 'nullable|array',
            'editing.answer' => 'nullable|string',
            'editing.mark' => 'required|integer|min:1',
            'editing.hint' => 'nullable|string',
            'editing.image' => 'nullable|string',
        ]);

        $question = QuestionBank::findOrFail($this->editing['id']);
        $question->update([
            'question' => $this->editing['question'],
            'question_type' => $this->editing['question_type'],
            'options' => $this->editing['options'],
            'answer' => $this->editing['answer'],
            'mark' => $this->editing['mark'],
            'hint' => $this->editing['hint'],
            'image' => $this->editing['image'],
        ]);

        // Refresh questions list
        $this->questions = $this->exam->questions->toArray();

        // Close modal
        $this->showEditModal = false;

        $this->emit('refreshComponent');

        // Show success notification
        Notification::make()
            ->title('Success')
            ->body('Question updated successfully.')
            ->success()
            ->send();
    }
    // Delete Question
    public function deleteQuestion($questionId)
    {
        QuestionBank::findOrFail($questionId)->delete();

        // Refresh questions list
        $this->questions = $this->exam->questions->toArray();

        // Show success notification
        Notification::make()
            ->title('Deleted')
            ->body('Question deleted successfully.')
            ->success()
            ->send();
    }

    public function render()
    {
        return view('livewire.view-exams');
    }
}
