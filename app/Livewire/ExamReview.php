<?php

namespace App\Livewire;

use Livewire\Component;

class ExamReview extends Component
{

    public $userAnswers = [];
    public $questions = [];

    public $timer = 0;

    public function mount($userAnswers, $questions, $timer)
    {
        $this->userAnswers = $userAnswers;
        $this->questions = $questions;
        $this->timer = $timer;
    }




    public function render()
    {
        return view('livewire.exam-review', [
           'userAnswers' => $this->userAnswers,
            'questions' => $this->questions,
            'timeRemaining'=> $this->time
        ]);
    }


}
