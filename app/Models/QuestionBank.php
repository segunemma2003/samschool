<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class QuestionBank extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'options' => 'array',
        'marks' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'marks' => 1,
        'question_type' => 'multiple_choice',
    ];

    // OPTIMIZED RELATIONSHIPS
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id')
            ->select(['id', 'subject_id', 'assessment_type', 'term_id', 'academic_year_id']);
    }

    // PERFORMANCE SCOPES
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('question_type', $type);
    }

    public function scopeByExam(Builder $query, int $examId): Builder
    {
        return $query->where('exam_id', $examId);
    }

 public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->join('exams', 'question_banks.exam_id', '=', 'exams.id')
                    ->join('subjects', 'exams.subject_id', '=', 'subjects.id')
                    ->where('subjects.teacher_id', $teacherId)
                    ->select('question_banks.*'); // Avoid column conflicts
    }

    public function scopeWithMinMarks(Builder $query, float $minMarks): Builder
    {
        return $query->where('marks', '>=', $minMarks);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // CACHED ATTRIBUTES
    public function getCorrectOptionAttribute(): ?array
    {
        if (!in_array($this->question_type, ['multiple_choice', 'true_false'])) {
            return null;
        }

        foreach ($this->options ?? [] as $option) {
            if ($option['is_correct'] ?? false) {
                return $option;
            }
        }

        return null;
    }

    public function getOptionsCountAttribute(): int
    {
        return count($this->options ?? []);
    }

    public function getQuestionPreviewAttribute(): string
    {
        return strlen($this->question) > 100
            ? substr(strip_tags($this->question), 0, 100) . '...'
            : strip_tags($this->question);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        return Cache::remember(
            "question_image_{$this->id}",
            3600, // 1 hour
            fn() => Storage::disk('s3')->url($this->image)
        );
    }

    public function getQuestionTypeDisplayAttribute(): string
    {
        return match ($this->question_type) {
            'multiple_choice' => 'Multiple Choice',
            'true_false' => 'True/False',
            'open_ended' => 'Open Ended',
            default => ucfirst(str_replace('_', ' ', $this->question_type)),
        };
    }

    public function getMarksDisplayAttribute(): string
    {
        return $this->marks . ' point' . ($this->marks != 1 ? 's' : '');
    }

    // VALIDATION METHODS
    public function hasValidOptions(): bool
    {
        if ($this->question_type === 'open_ended') {
            return !empty($this->answer);
        }

        $options = $this->options ?? [];

        if (empty($options)) {
            return false;
        }

        // Check if at least one option is marked as correct
        foreach ($options as $option) {
            if ($option['is_correct'] ?? false) {
                return true;
            }
        }

        return false;
    }

    public function isComplete(): bool
    {
        return !empty($this->question) &&
               $this->marks > 0 &&
               $this->hasValidOptions();
    }

    // UTILITY METHODS
    public function getCorrectAnswerText(): ?string
    {
        if ($this->question_type === 'open_ended') {
            return $this->answer;
        }

        $correctOption = $this->correct_option;
        return $correctOption['option'] ?? null;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        // Add computed attributes
        $array['question_preview'] = $this->question_preview;
        $array['question_type_display'] = $this->question_type_display;
        $array['marks_display'] = $this->marks_display;
        $array['is_complete'] = $this->isComplete();

        return $array;
    }

    // STATISTICS METHODS
    public static function getStatsForTeacher(int $teacherId): array
    {
        $cacheKey = "question_stats_teacher_{$teacherId}";

        return Cache::remember($cacheKey, 600, function () use ($teacherId) {
            $baseQuery = static::forTeacher($teacherId);

            return [
                'total' => $baseQuery->count(),
                'by_type' => [
                    'multiple_choice' => $baseQuery->byType('multiple_choice')->count(),
                    'true_false' => $baseQuery->byType('true_false')->count(),
                    'open_ended' => $baseQuery->byType('open_ended')->count(),
                ],
                'recent' => $baseQuery->recent(7)->count(),
                'total_marks' => $baseQuery->sum('marks'),
                'avg_marks' => round($baseQuery->avg('marks'), 2),
            ];
        });
    }

    public static function getStatsForExam(int $examId): array
    {
        $cacheKey = "question_stats_exam_{$examId}";

        return Cache::remember($cacheKey, 300, function () use ($examId) {
            $baseQuery = static::byExam($examId);

            return [
                'total' => $baseQuery->count(),
                'by_type' => [
                    'multiple_choice' => $baseQuery->byType('multiple_choice')->count(),
                    'true_false' => $baseQuery->byType('true_false')->count(),
                    'open_ended' => $baseQuery->byType('open_ended')->count(),
                ],
                'total_marks' => $baseQuery->sum('marks'),
                'incomplete' => $baseQuery->get()->filter(fn($q) => !$q->isComplete())->count(),
            ];
        });
    }

    // SEARCH FUNCTIONALITY
    public static function search(string $query): Builder
    {
        return static::query()
            ->where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('question', 'LIKE', "%{$query}%")
                            ->orWhere('answer', 'LIKE', "%{$query}%")
                            ->orWhere('hint', 'LIKE', "%{$query}%");
            });
    }

    // MODEL EVENTS
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($question) {
            // Clear related caches
            self::clearRelatedCaches($question);
        });

        static::deleted(function ($question) {
            // Clean up image file
            if ($question->image && Storage::disk('s3')->exists($question->image)) {
                Storage::disk('s3')->delete($question->image);
            }

            // Clear related caches
            self::clearRelatedCaches($question);
        });
    }

    private static function clearRelatedCaches($question): void
    {
        try {
            // Clear teacher stats
            if ($question->exam && $question->exam->subject) {
                Cache::forget("question_stats_teacher_{$question->exam->subject->teacher_id}");
            }

            // Clear exam stats
            Cache::forget("question_stats_exam_{$question->exam_id}");

            // Clear image cache
            Cache::forget("question_image_{$question->id}");

        } catch (\Exception $e) {
            \Log::warning('Error clearing question caches: ' . $e->getMessage());
        }
    }

    // BULK OPERATIONS
    public static function bulkUpdateMarks(array $questionIds, float $marks): int
    {
        $updated = static::whereIn('id', $questionIds)
            ->update(['marks' => $marks]);

        // Clear related caches
        foreach ($questionIds as $id) {
            Cache::forget("question_image_{$id}");
        }

        return $updated;
    }

    public static function bulkDelete(array $questionIds): int
    {
        $questions = static::whereIn('id', $questionIds)->get();

        // Clean up images
        foreach ($questions as $question) {
            if ($question->image && Storage::disk('s3')->exists($question->image)) {
                Storage::disk('s3')->delete($question->image);
            }
        }

        return static::whereIn('id', $questionIds)->delete();
    }
}
