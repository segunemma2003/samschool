<?php
namespace App\Traits;

use App\Models\SecurityLog;

trait HandlesQuizSecurity
{
    private int $tabSwitchCount = 0;
    private array $ipHistory = [];

    protected function enforceSecurityMeasures(): void
    {
        $this->trackIP();
        $this->enforceFullscreen();
        $this->preventMultipleWindows();
    }

    private function trackIP(): void
    {
        $currentIP = request()->ip();
        $this->ipHistory[] = [
            'ip' => $currentIP,
            'timestamp' => now(),
        ];

        if ($this->detectIPChange()) {
            $this->logSecurityIncident('IP_CHANGE');
        }
    }

    private function detectIPChange(): bool
    {
        if (count($this->ipHistory) < 2) return false;

        $uniqueIPs = collect($this->ipHistory)->pluck('ip')->unique();
        return $uniqueIPs->count() > 1;
    }

    private function enforceFullscreen(): void
    {
        $this->dispatch('enforceFullscreen');
    }

    private function preventMultipleWindows(): void
    {
        $this->dispatch('preventMultipleWindows');
    }

    protected function logSecurityIncident(string $type): void
    {
        SecurityLog::create([
            'exam_id' => $this->examId,
            'student_id' => $this->studentId,
            'incident_type' => $type,
            'details' => json_encode([
                'ip_history' => $this->ipHistory,
                'tab_switches' => $this->tabSwitchCount,
                'timestamp' => now(),
            ]),
        ]);
    }
}
