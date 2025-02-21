<?php

namespace App\Livewire;

use App\Models\CourseForm;
use App\Models\Exam;
use App\Models\ExamRecording;
use App\Models\QuizScore;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class Quiz extends Component
{
    use WithFileUploads;

    public $isRecording = false;
    public $showSuccessMessage = false;
    public $isLoading = false;
    public $userName = '';
    public $subject = '';
    public $studentId = null;
    public $quizTitle = '';
    public $duration = 0;
    public $currentQuestion = 0;
    public $questions = [];
    public $examId = null;
    public $recording = null;
    public $selectedAnswer = null;
    public $userAnswers = [];
    public $timeRemaining;
    public $timerActive = false;
    public $isSubmitted = false;
    public $isReviewing = false;
    public $courseFormId = null;
    public $finalScore = 0;
    public $totalScore = 0;
    public $loadingNext = false; // For loading state on "Next"
    public $loadingPrevious = false; // For loading state on "Previous"

    private $sessionKey = 'quiz_state';

    protected $listeners = [
        'submit' => 'submit',
        'timeUpdated' => 'updateTimer',
        'recordingStopped' => 'handleRecordingStopped',
    ];

    public function mount($record)
    {
        $exam = Exam::findOrFail($record);
        $this->examId = $exam->id;

        $user = Auth::user();
        $student = Student::whereEmail($user->email)->firstOrFail();
        $this->studentId = $student->id;

        $course = CourseForm::where('subject_id', $exam->subject_id)
            ->where('student_id', $student->id)
            ->where('academic_year_id', $exam->academic_year_id)
            ->firstOrFail();

        $this->courseFormId = $course->id;
        $this->userName = $student->name;
        $this->subject = $exam->subject->subjectDepot->name;
        $this->quizTitle = $exam->details;
        $this->duration = $exam->duration;
        $this->timeRemaining = $exam->duration * 60;

        $this->restoreState(); // Restore state on mount
        if(count($this->questions)< 1){
            $this->generateQuestions($exam);
        }
        if (!$this->hasShuffled()) {
            $this->markAsShuffled();
        } else {
            $this->questions = $this->restoreQuestionOrder($exam); // Restore shuffled order
        }
    }

    private function hasShuffled()
    {
        $state = session()->get($this->sessionKey . '_' . $this->examId . '_' . $this->studentId, []);
        return isset($state['shuffled']) && $state['shuffled'] === true;
    }

    private function markAsShuffled()
    {
        $state = session()->get($this->sessionKey . '_' . $this->examId . '_' . $this->studentId, []);
        $state['shuffled'] = true;
        $state['questionOrder'] = array_keys($this->questions); // Store shuffled order
        session()->put($this->sessionKey . '_' . $this->examId . '_' . $this->studentId, $state);
    }

    private function restoreQuestionOrder($exam)
    {
        $state = session()->get($this->sessionKey . '_' . $this->examId . '_' . $this->studentId, []);
        $questionOrder = $state['questionOrder'] ?? [];
        // dd($questionOrder);
        $orderedQuestions = [];
        foreach ($questionOrder as $key) {

            $orderedQuestions[] = $this->questions[$key]; // Use stored order
        }

        return $orderedQuestions;
    }

    public function handleRecordingStopped($blob)
    {
        // Convert Blob to file and save
        $path = 'exam_recordings/' . uniqid() . '.webm';
        Storage::disk('s3')->put($path, base64_decode($blob));

        ExamRecording::create([
            'exam_id' => $this->examId,
            'student_id' => $this->studentId,
            'recording_path' => $path,
            'recorded_at' => now(),
        ]);

        $this->recording = null;
    }

    public function handleCameraError()
    {
        session()->flash('error', 'Camera access is required for this exam.');
    }

    public function submitQuiz()
    {
        $this->isSubmitted = true;
        $this->isReviewing = true;
        $this->saveState(); // Save state before reviewing
    }

    public function generateQuestions($exam)
    {
        $this->questions = $exam->questions->toArray();
        shuffle($this->questions);
    }

    public function startTimer()
    {
        $this->timerActive = true;
    }

    #[On('updateTimer')]
    public function updateTimer($timer)
    {
        $this->timeRemaining = $timer;
        if ($this->timeRemaining <= 0 && !$this->isSubmitted) {
            $this->submit();
        }
        $this->saveState();
    }

    public function nextQuestion()
    {
        $this->saveCurrentAnswer();
        if ($this->currentQuestion < count($this->questions) - 1) {
            $this->currentQuestion++;
            $this->selectedAnswer = $this->userAnswers[$this->currentQuestion] ?? null; // Restore answer
        } else {
            $this->submitQuiz();
        }
        $this->saveState();
        $this->loadingNext = false;
    }

    public function previousQuestion()
    {
        $this->saveCurrentAnswer();
        if ($this->currentQuestion > 0) {
            $this->currentQuestion--;
            $this->selectedAnswer = $this->userAnswers[$this->currentQuestion] ?? null; // Restore answer
        }
        $this->saveState();
        $this->loadingPrevious = false;
    }

    private function saveCurrentAnswer()
    {
        // $this->userAnswers[$this->currentQuestion] = $this->selectedAnswer;
        if (isset($this->questions[$this->currentQuestion])) {
            $questionId = $this->questions[$this->currentQuestion]['id'];
            $this->userAnswers[$questionId] = $this->selectedAnswer;
        }
    }

    public function submitResult()
    {
        $this->isLoading = true;
        try {
            $this->isSubmitted = true;
            $this->saveCurrentAnswer();
        //   dd($this->userAnswers);
            // $totalScore = collect($this->questions)->sum(function ($q, $key) {

            //     return isset($this->userAnswers[$q['id']]) && $this->userAnswers[$q['id']] == $q['answer']
            //         ? $q['marks']
            //         : 0;
            // });

            $totalScore = collect($this->questions)->reduce(function ($carry, $q) {


                return $carry +  $q['marks'];
            }, 0);

            $total = collect($this->questions)->reduce(function ($carry, $q) {
                if (!isset($q['id'], $q['answer'], $q['marks'])) {
                    Log::error("Question data missing fields", ['question' => $q]);
                    return $carry;
                }


                return $carry + (isset($this->userAnswers[$q['id']]) && $this->userAnswers[$q['id']] == $q['answer']
                    ? $q['marks']
                    : 0);
            }, 0);

            // $totalScore = collect($this->questions)->sum(function ($q, $key) {

            //     return isset($this->userAnswers[$q['id']]) && $this->userAnswers[$q['id']] == $q['answer']
            //         ? $q['marks']
            //         : 0;
            // });

            Log::info($totalScore);
            Log::info($total);
            $this->finalScore = ($total/$totalScore)*100;
            QuizScore::updateOrCreate(
                [
                    'course_form_id' => $this->courseFormId,
                    'student_id' => $this->studentId,
                    'exam_id' => $this->examId
                ],
                ['total_score' => $this->finalScore, 'comments'=>"submitted"]
            );


            $this->isReviewing = false;
            $this->showSuccessMessage = true;
            // Clear session state after submission
        } finally {
            $this->isLoading = false;
        }
    }

    private function clearState()
    {
        session()->forget($this->sessionKey . '_' . $this->examId . '_' . $this->studentId);
    }

    private function saveState()
    {
        $state = [
            'timeRemaining' => $this->timeRemaining,
            'currentQuestion' => $this->currentQuestion,
            'userAnswers' => $this->userAnswers,
        ];
        session()->put($this->sessionKey . '_' . $this->examId . '_' . $this->studentId, $state);
    }

    private function restoreState()
    {
        $state = session()->get($this->sessionKey . '_' . $this->examId . '_' . $this->studentId, []);

        if (isset($state['timeRemaining'])) {
            // dd($state);
            $this->timeRemaining = $state['timeRemaining'];
            $this->currentQuestion = $state['currentQuestion'];
            $this->userAnswers = $state['userAnswers'];
        } else {
            // Initialize default values if no state exists
            $this->timeRemaining = $this->duration * 60;
            $this->currentQuestion = 0;
            $this->userAnswers = [];
        }
    }

    public function render()
    {
        return view('livewire.quiz', [
            'isSubmitted' => $this->isSubmitted,
            'timeRemaining' => $this->timeRemaining,
            'isRecording' => $this->isRecording,
            'loadingNext' => $this->loadingNext, // Pass loading state to the view
            'loadingPrevious' => $this->loadingPrevious, // Pass loading state to the view
            'finalScore' => $this->finalScore,
        ]);
    }
}
