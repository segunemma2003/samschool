<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class Quiz extends Component
{

    public $userName = 'John Doe';
    public $subject = 'Mathematics';
    public $quizTitle = 'Quiz 1';
    public $duration = 30; // duration in minutes
    public $currentQuestion = 0;
    public $questions = [];
    public $selectedAnswer = null;
    public $userAnswers = [];
    public $timeRemaining; // Total time remaining in seconds
    public $timerActive = false; // To track if the timer is active
    public $isSubmitted = false; // To track if the quiz has been submitted
    public $isReviewing = false;

    protected $listeners = ['submit' => 'submit'];
    public function mount()
    {
        $this->timeRemaining = $this->duration * 60; // Convert to seconds
        $this->generateQuestions();
        $this->startTimer(); // Start the timer on mount
    }


    public function submitQuiz()
    {
        $this->isSubmitted = true;
        $this->isReviewing = true; // Set to true when quiz is submitted
    }

    public function generateQuestions()
    {
        // Sample questions with different types
        $this->questions = [
            [
                'question' => 'What is the capital of France?',
                'type' => 'mcq',
                'options' => ['A' => 'Berlin', 'B' => 'Paris', 'C' => 'Madrid', 'D' => 'Rome'],
                'correct' => 'B'
            ],
            [
                'question' => 'The Earth is flat. ',
                'type' => 'true_false',
                'options' => [],
                'correct' => 'False'
            ],
            [
                'question' => 'What is the largest mammal in the world?',
                'type' => 'open_ended',
                'options' => [],
                'correct' => 'Blue Whale'
            ],
            [
                'question' => 'What is 2 + 2?',
                'type' => 'mcq',
                'options' => ['A' => '3', 'B' => '4', 'C' => '5', 'D' => '6'],
                'correct' => 'B'
            ],
            [
                'question' => 'The sun rises in the east. ',
                'type' => 'true_false',
                'options' => [],
                'correct' => 'True'
            ],
            [
                'question' => 'What is the chemical symbol for water?',
                'type' => 'mcq',
                'options' => ['A' => 'H2O', 'B' => 'O2', 'C' => 'CO2', 'D' => 'He'],
                'correct' => 'A'
            ],
            [
                'question' => 'Is the sky blue? ',
                'type' => 'true_false',
                'options' => [],
                'correct' => 'True'
            ],
            [
                'question' => 'Name the first planet in our solar system.',
                'type' => 'open_ended',
                'options' => [],
                'correct' => 'Mercury'
            ],
            [
                'question' => 'Which gas do plants absorb from the atmosphere?',
                'type' => 'mcq',
                'options' => ['A' => 'Oxygen', 'B' => 'Carbon Dioxide', 'C' => 'Nitrogen', 'D' => 'Hydrogen'],
                'correct' => 'B'
            ],
            [
                'question' => 'The Great Wall of China is visible from space. ',
                'type' => 'true_false',
                'options' => [],
                'correct' => 'False'
            ],
            [
                'question' => 'What is the hardest natural substance on Earth?',
                'type' => 'open_ended',
                'options' => [],
                'correct' => 'Diamond'
            ],
            [
                'question' => 'How many continents are there?',
                'type' => 'mcq',
                'options' => ['A' => '5', 'B' => '6', 'C' => '7', 'D' => '8'],
                'correct' => 'C'
            ],
            [
                'question' => 'Water boils at 100 degrees Celsius. ',
                'type' => 'true_false',
                'options' => [],
                'correct' => 'True'
            ],
            [
                'question' => 'Who wrote "Romeo and Juliet"?',
                'type' => 'open_ended',
                'options' => [],
                'correct' => 'William Shakespeare'
            ],
            [
                'question' => 'What is the main ingredient in guacamole?',
                'type' => 'mcq',
                'options' => ['A' => 'Tomato', 'B' => 'Avocado', 'C' => 'Pepper', 'D' => 'Onion'],
                'correct' => 'B'
            ],
            [
                'question' => 'Do humans have more than two legs? ',
                'type' => 'true_false',
                'options' => [],
                'correct' => 'False'
            ],
            [
                'question' => 'What is the speed of light in vacuum?',
                'type' => 'open_ended',
                'options' => [],
                'correct' => '299,792 km/s'
            ],
            [
                'question' => 'Which element has the atomic number 1?',
                'type' => 'mcq',
                'options' => ['A' => 'Helium', 'B' => 'Hydrogen', 'C' => 'Lithium', 'D' => 'Oxygen'],
                'correct' => 'B'
            ],
            [
                'question' => 'The heart is a muscle. ',
                'type' => 'true_false',
                'options' => [],
                'correct' => 'True'
            ],
        ];

        // Shuffle questions randomly
        shuffle($this->questions);
        // Limit to 20 questions
        $this->questions = array_slice($this->questions, 0, 20);
    }
    public function startTimer()
    {
        $this->timerActive = true;
        $this->dispatch('start-timer', [
            'timeRemaining' => $this->timeRemaining
        ]);
    }

    #[On('updateTimer')]
    public function updateTimer($timer)
    {
        $this->timeRemaining = $timer; // Ensure $timer is being passed correctly
    }


    public function nextQuestion()
    {
        $this->saveCurrentAnswer();
        if ($this->currentQuestion < count($this->questions) - 1) {
            $this->userAnswers[$this->currentQuestion] = $this->selectedAnswer; // Save the selected answer
            $this->currentQuestion++;
            $this->selectedAnswer = null; // Reset selected answer for next question

            // Dispatch an event to update the timer
            $this->dispatch('question-changed', $this->timeRemaining);
        }else{
           $this->submitQuiz();
        }
    }


    public function previousQuestion()
    {
        $this->saveCurrentAnswer();
        if ($this->currentQuestion > 0) {
            $this->userAnswers[$this->currentQuestion] = $this->selectedAnswer; // Save the selected answer
            $this->currentQuestion--;
            $this->selectedAnswer = null; // Reset selected answer for previous question

            // Dispatch an event to update the timer
            $this->dispatch('question-changed', $this->timeRemaining);
        }
    }

    private function saveCurrentAnswer()
    {
        $this->userAnswers[$this->currentQuestion] = $this->selectedAnswer; // Save the selected answer
    }


    public function goBackToQuiz()
    {
        $this->isReviewing = false; // Set to false when returning to quiz
        $this->currentQuestion = 0; // Reset to first question or manage as needed
    }

    public function submit()
    {
       $this->isSubmitted = true; // Set the submission state
       $this->saveCurrentAnswer();
       $this->dispatch('done');
       $this->dispatch('stop-recording');
       $this->dispatch('quiz-submitted');
        // Handle submission logic here (e.g., save to database)
    }

    public function review()
    {
        // Logic to display the review page with user answers
        return redirect()->route('exam.review', [
            'userAnswers' => $this->userAnswers,
            'questions' => $this->questions,
            'timer'=> $this->timeRemaining
        ]);
    }


    public function render()
    {
        return view('livewire.quiz', [
            'isSubmitted' => $this->isSubmitted, // Pass submission state to view
            'timeRemaining' => $this->timeRemaining, // Pass time remaining to view
        ]);
    }
}
