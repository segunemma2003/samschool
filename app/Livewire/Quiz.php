<?php

namespace App\Livewire;

use App\Models\CourseForm;
use App\Models\Exam;
use App\Models\ExamRecording;
use App\Models\QuizScore;
use App\Models\QuizSubmission;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Stancl\Tenancy\Concerns\UsesTenantModel;

class Quiz extends Component
{


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
    public $recordingPath = null;
    public $selectedAnswer = null;
    public $userAnswers = [];
    public $timeRemaining;
    public $timerActive = false;
    public $isSubmitted = false;
    public $isReviewing = false;
    public $courseFormId = null;

    protected $listeners = [
        'submit' => 'submit',
        'answerSelected' => 'saveAnswer',
        'timeUpdated' => 'updateTimer',
        'cameraError' => 'handleCameraError',
        'recordingStarted' => 'handleRecordingStarted',
        'recordingStopped' => 'handleRecordingStopped',
        'recordingUploaded' => 'handleRecordingUploaded'
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

        $this->loadState();
        $this->generateQuestions($exam);
        $this->startRecording();
        $this->startTimer();
    }

    private function loadState()
    {
        $this->userAnswers = session()->get("quiz_answers_{$this->examId}", []);
        $this->currentQuestion = session()->get("quiz_question_{$this->examId}", 0);
        $this->timeRemaining = session()->get("quiz_timer_{$this->examId}", $this->timeRemaining);
    }

    private function saveState()
    {
        session()->put("quiz_answers_{$this->examId}", $this->userAnswers);
        session()->put("quiz_question_{$this->examId}", $this->currentQuestion);
        session()->put("quiz_timer_{$this->examId}", $this->timeRemaining);
    }

    public function handleRecordingStarted()
    {
        $this->isRecording = true;
    }

    public function handleRecordingStopped()
    {
        $this->isRecording = false;
    }

    public function handleRecordingUploaded($path)
    {
        $this->recordingPath = $path;

        ExamRecording::create([
            'exam_id' => $this->examId,
            'student_id' => $this->studentId,
            'recording_path' => $path,
            'recorded_at' => now()
        ]);
    }

    public function handleCameraError()
    {
        session()->flash('error', 'Camera access is required for this exam. Please enable camera access and refresh the page.');
    }

    public function submitQuiz()
    {
        $this->isSubmitted = true;
        $this->isReviewing = true;
    }

    public function generateQuestions($exam)
    {
        $this->questions = $exam->questions->toArray();
        shuffle($this->questions);
    }

    public function startTimer()
    {
        $this->timerActive = true;
        $this->dispatch('start-timer', ['timeRemaining' => $this->timeRemaining]);
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
            $this->selectedAnswer = null;
            $this->dispatch('question-changed', $this->timeRemaining);
        } else {
            $this->submitQuiz();
        }
        $this->saveState();
    }

    public function previousQuestion()
    {
        $this->saveCurrentAnswer();
        if ($this->currentQuestion > 0) {
            $this->currentQuestion--;
            $this->selectedAnswer = null;
            $this->dispatch('question-changed', $this->timeRemaining);
        }
        $this->saveState();
    }

    private function saveCurrentAnswer()
    {
        $this->userAnswers[$this->currentQuestion] = $this->selectedAnswer;
    }

    public function submitResult()
    {
        $this->isLoading = true;
        try {
            $this->dispatch('stopRecording');
            $this->isSubmitted = true;
            $this->saveCurrentAnswer();
            $this->dispatch('done');

            QuizScore::updateOrCreate(
                ['course_form_id' => $this->courseFormId, 'student_id' => $this->studentId, 'exam_id' => $this->examId],
                ['total_score' => collect($this->questions)->sum(fn($q, $i) => $this->normalizeAnswer($this->userAnswers[$i]) == $this->normalizeAnswer($q['answer']) ? $q['marks'] : 0)]
            );

            $this->showSuccessMessage = true;
        } finally {
            $this->isLoading = false;
        }
    }

    public function render()
    {
        return view('livewire.quiz', [
            'isSubmitted' => $this->isSubmitted,
            'timeRemaining' => $this->timeRemaining,
            'isRecording' => $this->isRecording
        ]);
    }
}
