<?php

namespace App\Livewire;

use App\Models\QuizScore;
use App\Models\QuizSubmission;
use Filament\Notifications\Notification;
use Livewire\Component;

class ExamStudentDetails extends Component
{
    public $quizScore;
    public $studentDetails;
    public $courseForm;
    public $questions;

    protected $rules = [
        'questions.*.comment' => 'nullable|string|max:255',
    ];


    public function mount($quizscoreid)
    {

        // Fetch the QuizScore with related models
        $this->quizScore = QuizScore::with(['courseForm', 'studentDetailScores.question', 'student', 'exam'])->findOrFail($quizscoreid);

        // Extract the necessary data from relationships
        $this->courseForm = $this->quizScore->courseForm;

        // Query for QuizSubmissions related to this QuizScore
        $this->questions = QuizSubmission::with('question')
        ->where('quiz_score_id', $this->quizScore->id)
        ->get();

        // Debug output to verify the results
        // dd($this->questions);

        $this->studentDetails = $this->quizScore->student;
    }


    public function saveComments()
    {
        $this->validate();

        foreach ($this->questions as $question) {
            QuizSubmission::where('id', $question->id)
                ->update(['comment' => $question->comment]);
        }

        Notification::make()->title('Comments updated successfully.')->success()
        ->send();
    }

    public function render()
    {
        return view('livewire.exam-student-details');
    }
}
