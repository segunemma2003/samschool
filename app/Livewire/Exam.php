<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Exam extends Component
{
    public $currentQuestionIndex = 0;
    public $duration = 3600; // 60 minutes in seconds (1 hour exam)
    public $questions = [];
    public $answers = []; // Store user answers for each question
    public $timer; // Timer for countdown
    public $recording = true;
    public $examDuration = 3600;
    public $startTime;
public $timeLeft;

    public $isSubmitted = false; // Track whether the exam is initially submitted


    public function mount()
    {
        // Dummy questions data
        $this->questions = [
            [
                'text' => 'What is 2 + 2?',
                'type' => 'multiple_choice',
                'options' => ['3', '4', '5', '6'],
                'correctAnswer' => '4',
                'image' => null
            ],
            [
                'text' => 'What is the capital of France?',
                'type' => 'open_ended',
                'correctAnswer' => null,
                'image' => null
            ],
            [
                'text' => 'True or False: The Earth is flat.',
                'type' => 'true_false',
                'options' => ['True', 'False'],
                'correctAnswer' => 'False',
                'image' => null
            ]
        ];

        // Initialize answers array with nulls
        foreach ($this->questions as $index => $question) {
            $this->answers[$index] = null; // Initialize with null for unanswered questions
        }

        // Start the timer (optional if you're handling this on the frontend)
        $this->timer = $this->duration;
    }

    public function decrementTimer()
    {
        if ($this->timer > 0) {
            $this->timer--;
        } else {
            // Handle exam submission when time runs out
            $this->submitExam();
        }
    }

    public function updateTimer($remainingTime)
    {
        $this->timer = $remainingTime;
    }

    public function updatedDuration()
    {
        // You can implement any additional logic when duration updates
    }

    public function recordAnswer($answer)
    {
        // Record the user's answer for the current question
        $this->answers[$this->currentQuestionIndex] = $answer;
    }

    public function nextQuestion()
    {
        if ($this->currentQuestionIndex < count($this->questions) - 1) {
            $this->currentQuestionIndex++;
        }
    }

    public function previousQuestion()
    {
        if ($this->currentQuestionIndex > 0) {
            $this->currentQuestionIndex--;
        }
    }

    public function submitExam()
    {
        // Handle the submission of the exam
        // You can store the answers in the database, or perform further validation here
        Session::put('timer', $this->timer);
        Session::put('questions', $this->questions);
        Session::put('answers', $this->answers);
        return redirect()->route('exam.review');
        // Add logic to store or process answers
        // redirect or show the submission confirmation
    }

    public function startRecording()
    {
        $this->recording = true;
        // Implement recording logic here using JavaScript (e.g., use MediaRecorder API)
    }

    public function stopRecording()
    {
        $this->recording = false;
        // Implement stop recording logic here
    }

    public function finalSubmission()
    {
        // Process the final submission of answers
        // Add logic to save answers in the database

        $this->stopRecording(); // Stop camera recording

        // Redirect to the final submission confirmation page
        return Redirect::to('/exam/final-submission');
    }


    public function render()
    {
        return view('livewire.exam', [
            'currentQuestion' => $this->questions[$this->currentQuestionIndex],
            'timeLeft' => $this->timer,
            'totalQuestions' => count($this->questions),
        ]);
    }
}
