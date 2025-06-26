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
            'student_id',    // Foreign key on CourseForm table
            'id',            // Foreign key on Subject table
            'id',            // Local key on Student table
            'subject_id'     // Local key on CourseForm table
        );
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

    public function scopeWithArm($query, $armId)
    {
        return $query->where('arm_id', $armId);
    }

    public function scopeByGroup($query, $groupId)
    {
        return $query->where('group_id', $groupId);
    }
}
