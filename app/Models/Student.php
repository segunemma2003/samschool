<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class Student extends Model
{
    use HasFactory, HasRoles;
    protected $guarded = ['id'];

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

    public function arm(){
        return $this->belongsTo(Arm::class, 'arm_id');
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
