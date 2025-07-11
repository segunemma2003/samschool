<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;

class Student extends Model
{
    use HasFactory, HasRoles;
    protected $guarded = ['id'];

    protected $with = [];

     protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
    ];


public function scopeWithEssentials($query)
{
    return $query->with([
        'class:id,name,class_numeric',
        'guardian:id,name,email,phone',
        'arm:id,name'
    ]);
}

public function scopeWithAssignmentStats($query)
{
    return $query->withCount([
        'assignments as total_assignments',
        'assignments as submitted_assignments' => function ($q) {
            $q->wherePivot('status', 'submitted');
        }
    ]);
}

public function scopeWithMinimalData($query)
{
    return $query->select([
        'id', 'name', 'email', 'class_id', 'arm_id', 'guardian_id'
    ]);
}

   public function guardian()
    {
        return $this->belongsTo(Guardians::class, 'guardian_id')
            ->select(['id', 'name', 'email', 'phone']);
    }


    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id')
            ->select(['id', 'name', 'class_numeric', 'teacher_id']);
    }

    public function parent()
    {
        return $this->belongsTo(Guardians::class, 'guardian_id');
    }

    public function courseForms()
    {
        return $this->hasMany(CourseForm::class, 'student_id');
    }

    public function subjects()
    {
        return $this->hasManyThrough(
            Subject::class,
            CourseForm::class,
            'student_id',
            'id',
            'id',
            'subject_id'
        )->select(['subjects.id', 'subjects.name', 'subjects.code']);
    }


     public function arm()
    {
        return $this->belongsTo(Arm::class, 'arm_id')
            ->select(['id', 'name']);
    }

    // ADD THIS MISSING RELATIONSHIP METHOD
     public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(Assignment::class, 'assignment_student')
            ->withPivot(['file', 'status', 'total_score', 'answer', 'comments_score', 'created_at'])
            ->withTimestamps()
            ->orderByPivot('created_at', 'desc');
    }


    public function getAverageScoreAttribute(): float
    {
        return cache()->remember(
            "student_avg_score_{$this->id}",
            600, // 10 minutes
            function () {
                return $this->assignments()
                    ->wherePivot('status', 'submitted')
                    ->avg('assignment_student.total_score') ?? 0;
            }
        );
    }


    public static function loadWithEssentials(array $studentIds)
    {
        return static::whereIn('id', $studentIds)
            ->with([
                'class:id,name',
                'guardian:id,name,phone',
                'arm:id,name'
            ])
            ->get();
    }


    public static function processInChunks(callable $callback, int $chunkSize = 100)
        {
            return static::with(['class:id,name'])
                ->chunk($chunkSize, $callback);
        }


    // CACHED: Expensive aggregations
    public function getSubmittedAssignmentsCountAttribute()
    {
        return cache()->remember(
            "student_{$this->id}_submitted_assignments",
            300, // 5 minutes
            fn() => $this->assignments()->wherePivot('status', 'submitted')->count()
        );
    }

    // HELPER METHOD TO GET SUBMITTED ASSIGNMENTS
    public function submittedAssignments(): BelongsToMany
    {
        return $this->assignments()
            ->wherePivot('status', 'submitted');
    }

    // HELPER METHOD TO GET DRAFT ASSIGNMENTS
    public function draftAssignments(): BelongsToMany
    {
        return $this->assignments()
            ->wherePivot('status', 'draft');
    }

    // HELPER METHOD TO CHECK IF STUDENT HAS SUBMITTED AN ASSIGNMENT
    public function hasSubmittedAssignment(int $assignmentId): bool
    {
        return $this->assignments()
            ->where('assignment_id', $assignmentId)
            ->wherePivot('status', 'submitted')
            ->exists();
    }

    // HELPER METHOD TO GET ASSIGNMENT SUBMISSION
    public function getAssignmentSubmission(int $assignmentId)
    {
        return $this->assignments()
            ->where('assignment_id', $assignmentId)
            ->first()?->pivot;
    }

    public function bookLoans(): HasMany
    {
        return $this->hasMany(LibraryBookLoan::class, 'borrower_id')
            ->where('borrower_type', self::class);
    }

    public function currentLoans(): HasMany
    {
        return $this->bookLoans()->where('status', 'borrowed');
    }

    public function bookRequests(): HasMany
    {
        return $this->hasMany(LibraryBookRequest::class, 'requester_id')
            ->where('requester_type', self::class);
    }


    public function scopeInClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }


    public function scopeForClass($query, $classId)
{
    return $query->where('class_id', $classId)
        ->with(['guardian:id,name,phone']);
}

    public function scopeWithArm($query, $armId)
    {
        return $query->where('arm_id', $armId);
    }

    public function scopeByGroup($query, $groupId)
    {
        return $query->where('group_id', $groupId);
    }


    public function group()
    {
        return $this->belongsTo(StudentGroup::class, 'group_id')
            ->select(['id', 'name']);
    }
}
