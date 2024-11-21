<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseForm extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function subject(){
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function academy(){
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function term(){
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }
}
