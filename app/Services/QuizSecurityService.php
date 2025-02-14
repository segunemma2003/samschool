<?php

namespace App\Services;

class QuizSecurityService
{
    private $session;
    private $exam;
    private $student;

    public function __construct($exam, $student)
    {
        $this->exam = $exam;
        $this->student = $student;
        $this->session = "quiz_session_{$exam->id}_{$student->id}";
    }

    public function validateSession(): bool
    {
        return cache()->get($this->session) === null;
    }

    public function startSession(): void
    {
        cache()->put($this->session, [
            'started_at' => now(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], now()->addHours(24));
    }

    public function endSession(): void
    {
        cache()->forget($this->session);
    }
}
