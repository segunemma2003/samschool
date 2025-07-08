<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use App\Services\S3FileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Assignment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'deadline' => 'datetime',
        'weight_mark' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'available',
        'weight_mark' => 0,
    ];
    protected $with = [];

    // OPTIMIZED RELATIONSHIPS with specific field selection
    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id')
            ->select(['id', 'name', 'class_numeric', 'teacher_id']);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class, 'term_id')
            ->select(['id', 'name', 'status']);
    }

    public function academy(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_id')
            ->select(['id', 'title', 'year', 'status']);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id')
            ->select(['id', 'name', 'code', 'teacher_id']);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id')
            ->select(['id', 'name', 'email', 'designation']);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'assignment_student')
            ->withPivot([
                'file',
                'status',
                'total_score',
                'answer',
                'comments_score',
                'created_at',
                'updated_at'
            ])
            ->withTimestamps();
    }

    // S3 FILE HANDLING METHODS
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file) {
            return null;
        }

        $s3Service = app(S3FileService::class);
        return $s3Service->getTemporaryUrl($this->file, 10); // 10-minute expiry
    }

    public function getFileMetadata(): ?array
    {
        if (!$this->file) {
            return null;
        }

        $s3Service = app(S3FileService::class);
        return $s3Service->getFileMetadata($this->file);
    }

    public function hasFile(): bool
    {
        if (!$this->file) {
            return false;
        }

        $s3Service = app(S3FileService::class);
        return $s3Service->fileExists($this->file);
    }

    public function getFilename(): ?string
    {
        if (!$this->file) {
            return null;
        }

        return basename($this->file);
    }

    public function downloadFile(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (!$this->file) {
            abort(404, 'File not found');
        }

        $s3Service = app(S3FileService::class);
        return $s3Service->downloadFile($this->file, $this->getFilename());
    }

    // PERFORMANCE SCOPES - FIXED PIVOT QUERIES
    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'available')
                    ->where('deadline', '>=', now());
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('deadline', '<', now())
                    ->where('status', '!=', 'closed');
    }

    public function scopeForClass(Builder $query, int $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForSubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForTerm(Builder $query, int $termId): Builder
    {
        return $query->where('term_id', $termId);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // FIXED: Correct pivot table query
    public function scopeWithSubmissions(Builder $query): Builder
    {
        return $query->whereHas('students', function (Builder $q) {
            $q->where('assignment_student.status', 'submitted');
        });
    }

    // CACHED ATTRIBUTES for better performance
    public function getTotalStudentsAnsweredAttribute(): int
    {
        return Cache::remember("assignment_{$this->id}_submitted_count", 300, function () {
            return $this->students()
                ->where('assignment_student.status', 'submitted')
                ->count();
        });
    }


    public function getAttachmentUrlAttribute()
    {
        if (!$this->attachment) return null;

        return cache()->remember(
            "message_attachment_{$this->id}",
            3600,
            fn() => Storage::disk('s3')->url($this->attachment)
        );
    }
    public function getTotalStudentsInClassAttribute(): int
    {
        $cacheKey = "assignment_{$this->id}_class_total";

        return Cache::remember($cacheKey, 600, function () {
            if (!$this->class_id) {
                return 0;
            }

            // Count students in the class
            return \App\Models\Student::where('class_id', $this->class_id)->count();
        });
    }

    public function getSubmissionRateAttribute(): float
    {
        $total = $this->total_students_in_class;
        if ($total === 0) {
            return 0;
        }

        return round(($this->total_students_answered / $total) * 100, 1);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->deadline && $this->deadline->isPast();
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'available' && !$this->is_overdue;
    }

    public function getDaysRemainingAttribute(): int
    {
        if (!$this->deadline || $this->is_overdue) {
            return 0;
        }

        return now()->diffInDays($this->deadline, false);
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->is_overdue) {
            return 'danger';
        }

        if ($this->days_remaining <= 1) {
            return 'warning';
        }

        if ($this->status === 'available') {
            return 'success';
        }

        return 'gray';
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'available' => $this->is_overdue ? 'heroicon-m-clock' : 'heroicon-m-check-circle',
            'closed' => 'heroicon-m-lock-closed',
            'draft' => 'heroicon-m-document',
            default => 'heroicon-m-clipboard-document',
        };
    }

    public function getExcerptAttribute(): string
    {
        if ($this->description) {
            return Str::limit(strip_tags($this->description), 100);
        }

        return 'No description provided';
    }

    // UTILITY METHODS - FIXED PIVOT QUERIES
    public function answeredStudents()
    {
        return $this->students()->where('assignment_student.status', 'submitted');
    }

    public function pendingStudents()
    {
        return $this->students()->where('assignment_student.status', 'draft');
    }

    public function getStudentPivotAttribute()
    {
        $studentId = auth()->user()?->student?->id;

        if (!$studentId) {
            return null;
        }

        return $this->students->where('id', $studentId)->first()?->pivot;
    }

    public function hasStudentSubmitted(int $studentId): bool
    {
        return $this->students()
            ->where('assignment_student.student_id', $studentId)
            ->where('assignment_student.status', 'submitted')
            ->exists();
    }

    public function getStudentSubmission(int $studentId)
    {
        return $this->students()
            ->where('assignment_student.student_id', $studentId)
            ->first()?->pivot;
    }

    public function canBeEditedBy(User $user): bool
    {
        // Only the teacher who created it or admin can edit
        $teacher = Teacher::where('email', $user->email)->first();

        return $teacher && $this->teacher_id === $teacher->id;
    }

    public function canBeViewedBy(User $user): bool
    {
        // Teacher, admin, or students in the class can view
        $teacher = Teacher::where('email', $user->email)->first();

        if ($teacher && $this->teacher_id === $teacher->id) {
            return true;
        }

        // Check if user is a student in this class
        if ($user->user_type === 'student') {
            $student = $user->student;
            return $student && $student->class_id === $this->class_id;
        }

        return false;
    }

    // STATISTICS METHODS - FIXED QUERIES
    public static function getStatsForTeacher(int $teacherId): array
    {
        $cacheKey = "assignment_stats_teacher_{$teacherId}";

        return Cache::remember($cacheKey, 600, function () use ($teacherId) {
            $baseQuery = static::where('teacher_id', $teacherId);

            return [
                'total' => $baseQuery->count(),
                'active' => $baseQuery->where('status', 'available')
                    ->where('deadline', '>=', now())
                    ->count(),
                'overdue' => $baseQuery->where('deadline', '<', now())
                    ->where('status', '!=', 'closed')
                    ->count(),
                'recent' => $baseQuery->where('created_at', '>=', now()->subDays(7))
                    ->count(),
                'with_submissions' => $baseQuery->whereHas('students', function (Builder $q) {
                    $q->where('assignment_student.status', 'submitted');
                })->count(),
            ];
        });
    }

    public function getSubmissionStats(): array
    {
        $cacheKey = "assignment_{$this->id}_submission_stats";

        return Cache::remember($cacheKey, 300, function () {
            // Get all submissions for this assignment
            $submissions = DB::table('assignment_student')
                ->where('assignment_id', $this->id)
                ->get();

            $stats = [
                'total_submissions' => $submissions->count(),
                'submitted' => $submissions->where('status', 'submitted')->count(),
                'draft' => $submissions->where('status', 'draft')->count(),
                'graded' => $submissions->whereNotNull('total_score')->count(),
                'avg_score' => 0,
            ];

            $gradedSubmissions = $submissions->whereNotNull('total_score');
            if ($gradedSubmissions->count() > 0) {
                $stats['avg_score'] = round($gradedSubmissions->avg('total_score'), 1);
            }

            return $stats;
        });
    }

    // MODEL EVENTS
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($assignment) {
            if (!$assignment->teacher_id) {
                $user = auth()->user();
                if ($user) {
                    $teacher = Teacher::where('email', $user->email)->first();
                    if ($teacher) {
                        $assignment->teacher_id = $teacher->id;
                    }
                }
            }
        });

        static::saved(function ($assignment) {
            // Clear related caches when assignment is updated
            Cache::forget("assignment_{$assignment->id}_submitted_count");
            Cache::forget("assignment_{$assignment->id}_class_total");
            Cache::forget("assignment_{$assignment->id}_submission_stats");
            Cache::forget("assignment_stats_teacher_{$assignment->teacher_id}");
        });

        static::deleting(function ($assignment) {
            // Clean up file when assignment is deleted
            if ($assignment->file) {
                $s3Service = app(S3FileService::class);
                $s3Service->deleteFile($assignment->file);
            }

            // Clear caches
            Cache::forget("assignment_{$assignment->id}_submitted_count");
            Cache::forget("assignment_{$assignment->id}_class_total");
            Cache::forget("assignment_{$assignment->id}_submission_stats");
            Cache::forget("assignment_stats_teacher_{$assignment->teacher_id}");
        });
    }

    // SEARCH FUNCTIONALITY
    public static function search(string $query): Builder
    {
        return static::query()
            ->where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('title', 'LIKE', "%{$query}%")
                            ->orWhere('description', 'LIKE', "%{$query}%");
            });
    }

    // BULK OPERATIONS
    public static function bulkUpdateStatus(array $assignmentIds, string $status): int
    {
        $updated = static::whereIn('id', $assignmentIds)
            ->update(['status' => $status]);

        // Clear related caches efficiently
        Cache::tags(['assignments'])->flush();

        return $updated;
    }
}
