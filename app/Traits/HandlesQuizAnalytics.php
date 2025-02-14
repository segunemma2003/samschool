<?php
namespace App\Traits;

use App\Models\QuestionAnalytics;
use App\Models\QuestionBank;
use Illuminate\Support\Facades\DB;

trait HandlesQuizAnalytics
{
    protected function trackQuestionAnalytics(int $questionId, string $answer, float $timeSpent): void
    {
        QuestionAnalytics::create([
            'exam_id' => $this->examId,
            'question_bank_id' => $questionId,
            'student_id' => $this->studentId,
            'answer' => $answer,
            'time_spent' => $timeSpent,
            'is_correct' => $this->isAnswerCorrect($questionId, $answer),
        ]);
    }

    protected function updateQuestionDifficulty(int $questionId): void
    {
        $analytics = QuestionAnalytics::where('question_id', $questionId)
            ->select([
                DB::raw('COUNT(*) as total_attempts'),
                DB::raw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_attempts'),
                DB::raw('AVG(time_spent) as avg_time_spent'),
            ])
            ->first();

        $difficultyScore = $this->calculateDifficultyScore($analytics);

        QuestionBank::where('id', $questionId)->update([
            'difficulty_level' => $difficultyScore,
            'avg_time_spent' => $analytics->avg_time_spent,
        ]);
    }

    private function calculateDifficultyScore($analytics): float
    {
        $correctRate = $analytics->correct_attempts / $analytics->total_attempts;
        $normalizedTime = min($analytics->avg_time_spent / 120, 1); // Normalize to 2 minutes max

        return (1 - $correctRate) * 0.7 + $normalizedTime * 0.3;
    }
}
