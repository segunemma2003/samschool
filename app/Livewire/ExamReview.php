<?php

namespace App\Livewire;

use Livewire\Component;

class ExamReview extends Component
{

    public $questions;
    public $answers;
    public $totalQuestions;

    public function mount($questions, $answers)
    {
        // Assume you pass the answers and questions from the Exam component
        $this->questions = $questions;
        $this->answers = $answers;
        $this->totalQuestions = count($questions);
    }

    public function finalSubmission()
    {
        // Logic to handle the final submission of the exam
        // Save the answers to the database or handle grading
        // Redirect to the final submission confirmation page
        return redirect()->to('/exam/final-submission');
    }


    public function render()
    {
        return view('livewire.exam-review', [
            'questions' => $this->questions,
            'answers' => $this->answers,
            'totalQuestions' => $this->totalQuestions,
        ]);
    }


}
