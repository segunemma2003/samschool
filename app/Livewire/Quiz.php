<?php

namespace App\Livewire;

use App\Models\CourseForm;
use App\Models\Exam;
use App\Models\QuizScore;
use App\Models\QuizSubmission;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;


class Quiz extends Component
{

    public $showSuccessMessage = false;
    public $isLoading = false;
    public $userName = '';
    public $subject = '';
    public $studentId = null;
    public $quizTitle = '';
    public $duration = 0; // duration in minutes
    public $currentQuestion = 0;
    public $questions = [];
    public $examId = null;
    public $selectedAnswer = null;
    public $userAnswers = [];
    public $timeRemaining; // Total time remaining in seconds
    public $timerActive = false; // To track if the timer is active
    public $isSubmitted = false; // To track if the quiz has been submitted
    public $isReviewing = false;
    public $courseFormId = null;

    protected $listeners = ['submit' => 'submit'];
    protected $refresh = ['isLoading','showSuccessMessage'];
    public function mount($record)
    {
        $exam = Exam::where('id', $record)->first();
        $this->examId = $exam->id;
        // dd($exam);
        $userId = Auth::id();
        $user= User::whereId($userId)->first();
        $student = Student::whereEmail($user->email)->first();
        $this->studentId =  $student->id;
        $course = CourseForm::where('subject_id', $exam->subject_id)
        ->where('student_id', $student->id)
        ->where('academic_year_id', $exam->academic_year_id)
        ->first();
        // dd($course);
        $this->courseFormId = $course->id;
        $this->userName = $student->name;
        $this->subject = $exam->subject->subjectDepot->name;
        $this->quizTitle = $exam->details;
        // dd($exam);
        $this->duration = $exam->duration;
        $this->timeRemaining = $exam->duration * 60; // Convert to seconds
        $this->generateQuestions($exam);
        $this->startTimer(); // Start the timer on mount
    }


    public function submitQuiz()
    {
        $this->isSubmitted = true;
        $this->isReviewing = true; // Set to true when quiz is submitted
    }

    public function generateQuestions($exam)
    {
        // dd($exam);
        // Sample questions with different types

        // dd($exam->questions);
        $this->questions = $exam->questions->toArray();

        // dd($this->questions);


        // Shuffle questions randomly
        shuffle($this->questions);
        // Limit to 20 questions
        // $this->questions = array_slice($this->questions, 0, 20);
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
        if ($this->timeRemaining <= 0 && !$this->isSubmitted) {
            $this->submit();
        }
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

    public function submitResult()
{
    $this->isLoading = true; // Start loading

    try {
        // Your existing submission logic
        $this->isSubmitted = true;
        $this->saveCurrentAnswer();
        $this->dispatch('done');
        $this->dispatch('quiz-submitted');
        $totalScore = 0;

        foreach ($this->questions as $index => $question) {
            if ($this->userAnswers[$index] == $question['answer']) {
                $totalScore += $question['marks'];
            }
        }

        $quizScore = QuizScore::firstOrNew(
            ['course_form_id' => $this->courseFormId, 'student_id' => $this->studentId, 'exam_id'=>$this->examId],
            ['comments' => '']
        );

        $quizScore->total_score = $totalScore;
        $quizScore->save();

        foreach ($this->questions as $index => $question) {
            $quizSubmission = QuizSubmission::firstOrNew(
                [
                    'course_form_id' => $this->courseFormId,
                    'student_id' => $this->studentId,
                    'quiz_score_id' => $quizScore->id,
                    'question_id' => $question['id'],
                    'exam_id'=>$this->examId
                ],
                ['comments' => '']
            );

            $quizSubmission->correct = $this->userAnswers[$index] == $question['answer'];
            $quizSubmission->answer = $this->userAnswers[$index] ?? ' ';
            $quizSubmission->score = $this->userAnswers[$index] == $question['answer'] ? $question['marks'] : 0;
            $quizSubmission->save();
        }

        $this->isLoading = false;
        $this->isReviewing= false;
        $this->showSuccessMessage = true;
        // $this->dispatch('refresh');
        // dd($this->showSuccessMessage);
    } finally {
        $this->isLoading = false; // Stop loading
    }
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
            // 'isLoading' => $this->isLoading
        ]);
    }
}
