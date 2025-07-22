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
    public $loadingNext = false;
    public $loadingPrevious = false;

    private $sessionKey = 'quiz_state';
    private $lastSaveTime = 0;
    private $saveThrottleSeconds = 10; // Save state every 10 seconds max

    protected $listeners = [
        'submit' => 'submit',
        'timeUpdated' => 'updateTimer',
        'recordingStopped' => 'handleRecordingStopped',
    ];

    public function mount($record)
    {
        try {
            // Eager load relationships to prevent N+1 queries
            $exam = Exam::with(['subject.subjectDepot', 'questions'])->findOrFail($record);
            $this->examId = $exam->id;

            $user = Auth::user();
            $student = Student::whereEmail($user->email)->firstOrFail();
            $this->studentId = $student->id;

            // Single query with proper relationships
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

            $this->initializeQuizState($exam);
        } catch (\Exception $e) {
            Log::error('Quiz mount error: ' . $e->getMessage());
            session()->flash('error', 'Failed to load quiz. Please try again.');
        }
    }

    private function initializeQuizState($exam)
    {
        $this->restoreState();

        // Only generate questions if we don't have any or if state is invalid
        if (empty($this->questions) || !$this->isStateValid($exam)) {
            $this->generateQuestions($exam);
            $this->markAsShuffled();
        } elseif ($this->hasShuffled()) {
            $this->questions = $this->restoreQuestionOrder($exam);
        }
    }

    private function isStateValid($exam)
    {
        // Check if current questions match the exam
        if (count($this->questions) !== $exam->questions->count()) {
            return false;
        }

        // Check if question IDs exist in current exam
        $examQuestionIds = $exam->questions->pluck('id')->toArray();
        foreach ($this->questions as $question) {
            if (!in_array($question['id'] ?? null, $examQuestionIds)) {
                return false;
            }
        }

        return true;
    }

    private function hasShuffled()
    {
        $sessionKey = $this->getSessionKey();
        return session()->has($sessionKey . '.shuffled');
    }

    private function markAsShuffled()
    {
        $sessionKey = $this->getSessionKey();
        $state = session()->get($sessionKey, []);
        $state['shuffled'] = true;
        $state['questionOrder'] = array_keys($this->questions);
        session()->put($sessionKey, $state);
    }

    private function restoreQuestionOrder($exam)
    {
        $sessionKey = $this->getSessionKey();
        $state = session()->get($sessionKey, []);
        $questionOrder = $state['questionOrder'] ?? [];

        $orderedQuestions = [];
        $currentQuestions = $this->questions;

        foreach ($questionOrder as $key) {
            // Add bounds checking to prevent undefined index errors
            if (isset($currentQuestions[$key])) {
                $orderedQuestions[] = $currentQuestions[$key];
            }
        }

        // If we lost questions due to bounds issues, regenerate
        if (count($orderedQuestions) !== count($currentQuestions)) {
            Log::warning('Question order mismatch, regenerating questions');
            $this->generateQuestions($exam);
            $this->markAsShuffled();
            return $this->questions;
        }

        return $orderedQuestions;
    }

    private function getSessionKey()
    {
        // Ensure components are never null to prevent key collisions
        $examId = $this->examId ?? 'unknown';
        $studentId = $this->studentId ?? 'unknown';
        return $this->sessionKey . '_' . $examId . '_' . $studentId;
    }

    public function handleRecordingStopped($blob)
    {
        try {
            $path = 'exam_recordings/' . uniqid() . '.webm';
            Storage::disk('s3')->put($path, base64_decode($blob));

            ExamRecording::create([
                'exam_id' => $this->examId,
                'student_id' => $this->studentId,
                'recording_path' => $path,
                'recorded_at' => now(),
            ]);

            $this->recording = null;
        } catch (\Exception $e) {
            Log::error('Recording save error: ' . $e->getMessage());
        }
    }

    public function handleCameraError()
    {
        session()->flash('error', 'Camera access is required for this exam.');
    }

    public function submitQuiz()
    {
        $this->isSubmitted = true;
        $this->isReviewing = true;
        $this->saveState(true); // Force save on submission
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

        // Quiz should never stop automatically - removed auto-submit condition

        // Throttle state saving to prevent excessive session writes
        $this->saveStateThrottled();
    }

    private function saveStateThrottled()
    {
        $currentTime = time();
        if ($currentTime - $this->lastSaveTime >= $this->saveThrottleSeconds) {
            $this->saveState();
            $this->lastSaveTime = $currentTime;
        }
    }

    public function nextQuestion()
    {
        $this->saveCurrentAnswer();

        if ($this->currentQuestion < count($this->questions) - 1) {
            $this->currentQuestion++;
            $this->loadCurrentAnswer();
        } else {
            $this->submitQuiz();
        }

        $this->saveState(true); // Force save on navigation
        $this->loadingNext = false;
    }

    public function previousQuestion()
    {
        $this->saveCurrentAnswer();

        if ($this->currentQuestion > 0) {
            $this->currentQuestion--;
            $this->loadCurrentAnswer();
        }

        $this->saveState(true); // Force save on navigation
        $this->loadingPrevious = false;
    }

    private function saveCurrentAnswer()
    {
        if (isset($this->questions[$this->currentQuestion]['id'])) {
            $questionId = $this->questions[$this->currentQuestion]['id'];
            $this->userAnswers[$questionId] = $this->selectedAnswer;
        }
    }

    private function loadCurrentAnswer()
    {
        if (isset($this->questions[$this->currentQuestion]['id'])) {
            $questionId = $this->questions[$this->currentQuestion]['id'];
            $this->selectedAnswer = $this->userAnswers[$questionId] ?? null;
        }
    }

    public function submitResult()
    {
        $this->isLoading = true;

        try {
            $this->isSubmitted = true;
            $this->saveCurrentAnswer();

            // Calculate scores - store literal score, not percentage
            $maxPossibleScore = collect($this->questions)->sum('marks');
            $achievedScore = $this->calculateAchievedScore();

            // Prevent division by zero for percentage calculation (for display only)
            if ($maxPossibleScore <= 0) {
                throw new \Exception('Invalid exam configuration: no marks available');
            }

            // Store literal achieved score instead of percentage
            $this->finalScore = $achievedScore;
            $percentageScore = ($achievedScore / $maxPossibleScore) * 100;

            Log::info('Quiz submission', [
                'exam_id' => $this->examId,
                'student_id' => $this->studentId,
                'max_score' => $maxPossibleScore,
                'achieved_score' => $achievedScore,
                'percentage' => $percentageScore
            ]);

            QuizScore::updateOrCreate(
                [
                    'course_form_id' => $this->courseFormId,
                    'student_id' => $this->studentId,
                    'exam_id' => $this->examId
                ],
                [
                    'total_score' => $achievedScore, // Store literal score
                    'comments' => "submitted"
                ]
            );

            $this->isReviewing = false;
            $this->showSuccessMessage = true;
            $this->clearState();

        } catch (\Exception $e) {
            Log::error('Quiz submission error: ' . $e->getMessage());
            session()->flash('error', 'Failed to submit quiz. Please try again.');
        } finally {
            $this->isLoading = false;
        }
    }

    private function calculateAchievedScore()
    {
        return collect($this->questions)->reduce(function ($carry, $question) {
            // Validate question structure
            if (!isset($question['id'], $question['answer'], $question['marks'])) {
                Log::warning('Question missing required fields', ['question' => $question]);
                return $carry;
            }

            $questionId = $question['id'];
            $correctAnswer = $question['answer'];
            $marks = $question['marks'];
            $userAnswer = $this->userAnswers[$questionId] ?? null;

            return $carry + ($userAnswer == $correctAnswer ? $marks : 0);
        }, 0);
    }

    private function clearState()
    {
        $sessionKey = $this->getSessionKey();
        session()->forget($sessionKey);
    }

    private function saveState($force = false)
    {
        if (!$force && !$this->shouldSaveState()) {
            return;
        }

        $sessionKey = $this->getSessionKey();
        $state = [
            'timeRemaining' => $this->timeRemaining,
            'currentQuestion' => $this->currentQuestion,
            'userAnswers' => $this->userAnswers,
            'lastSaved' => time(),
        ];

        session()->put($sessionKey, array_merge(session()->get($sessionKey, []), $state));
    }

    private function shouldSaveState()
    {
        // Don't save if exam is already submitted
        return !$this->isSubmitted;
    }

    private function restoreState()
    {
        $sessionKey = $this->getSessionKey();
        $state = session()->get($sessionKey, []);

        if (!empty($state)) {
            $this->timeRemaining = $state['timeRemaining'] ?? ($this->duration * 60);
            $this->currentQuestion = max(0, min($state['currentQuestion'] ?? 0, count($this->questions) - 1));
            $this->userAnswers = $state['userAnswers'] ?? [];

            // Load current answer after restoring state
            $this->loadCurrentAnswer();
        } else {
            // Initialize default values
            $this->timeRemaining = $this->duration * 60;
            $this->currentQuestion = 0;
            $this->userAnswers = [];
        }
    }

    public function render()
    {
        return view('livewire.quizes', [
            'isSubmitted' => $this->isSubmitted,
            'timeRemaining' => $this->timeRemaining, // Fixed: was $this->time
            'isRecording' => $this->isRecording,
            'loadingNext' => $this->loadingNext,
            'loadingPrevious' => $this->loadingPrevious,
            'finalScore' => $this->finalScore,
        ]);
    }
}
