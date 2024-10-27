<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id'); // Assuming your class model is SchoolClass
    }

    public function section()
    {
        return $this->belongsTo(SchoolSection::class, 'section_id'); // Assuming your class model is SchoolClass
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id'); // Assuming your class model is SchoolClass
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'assignment_student')
                    ->withPivot('file', 'status', 'total_score', 'answer', 'comments_score', 'updated_at')
                    ->withTimestamps();
    }
    public function getTotalStudentsAnsweredAttribute()
    {
        return $this->students()->wherePivot('status', 'submitted')->count();
    }

    public function answeredStudents()
    {
        return $this->students()->wherePivot('status', 'submitted');
    }

}
