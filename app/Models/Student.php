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

    public function guardian()
    {
        // Choose the appropriate relationship type:

        // If one student has one guardian:
        return $this->belongsTo(Guardians::class);

        // If one student can have multiple guardians:
        // return $this->hasMany(Guardian::class);

        // // If many students can have many guardians (many-to-many):
        // return $this->belongsToMany(Guardian::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
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
        return $this->belongsTo(Arm::class, 'arm_id');
    }

    // ADD THIS MISSING RELATIONSHIP METHOD
    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(Assignment::class, 'assignment_student')
            ->withPivot([
                'file',
                'status',
                'total_score',
                'answer',
                'comments_score',
                'teacher_id',
                'created_at',
                'updated_at'
            ])
            ->withTimestamps();
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
}
