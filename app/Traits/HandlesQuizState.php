<?php
namespace App\Traits;

trait HandlesQuizState
{
    private string $sessionKey;
    private array $stateData = [];

    protected function initializeState(): void
    {
        $this->sessionKey = "quiz_state_{$this->examId}_{$this->studentId}";
        $this->stateData = cache()->get($this->sessionKey, []);
    }

    protected function saveState(): void
    {
        $state = [
            'timeRemaining' => $this->timeRemaining,
            'currentQuestion' => $this->currentQuestion,
            'userAnswers' => $this->userAnswers,
            'flaggedQuestions' => $this->flaggedQuestions,
            'tabSwitchCount' => $this->tabSwitchCount ?? 0,
            'lastActive' => now(),
        ];

        cache()->put($this->sessionKey, $state, now()->addHours(24));
    }

    protected function restoreState(): void
    {
        if (!empty($this->stateData)) {
            foreach ($this->stateData as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    protected function clearState(): void
    {
        cache()->forget($this->sessionKey);
    }
}
