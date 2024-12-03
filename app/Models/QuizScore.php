<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizScore extends Model
{
    use HasFactory;
    protected $guarded =['id'];


    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function studentDetailScores()
    {
        return $this->hasMany(QuizSubmission::class, 'course_form_id');
    }

    public function courseForm()
    {
        return $this->belongsTo(CourseForm::class, 'course_form_id');
    }



 public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }
}
