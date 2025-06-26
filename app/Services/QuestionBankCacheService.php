<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class QuestionBankCacheService
{
    public static function clearTeacherCaches(int $teacherId): void
    {
        $keys = [
            "teacher_exam_options_{$teacherId}",
            "teacher_questions_count_{$teacherId}",
            "question_stats_teacher_{$teacherId}",
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    public static function clearExamCaches(int $examId): void
    {
        Cache::forget("question_stats_exam_{$examId}");
        Cache::forget("exam_{$examId}_questions_count");
    }
}
