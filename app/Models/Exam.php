<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function academic()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function questions()
    {
        return $this->hasMany(QuestionBank::class, 'exam_id');
    }

    public function examScore($studentId)
    {
        return $this->hasOne(QuizScore::class, 'exam_id')
        ->where('student_id', $studentId);
    }

    public function allStudentsScore()
    {
        return $this->hasMany(QuizScore::class, 'exam_id');
    }

    public function studentsWithScores()
    {
        return $this->hasMany(QuizScore::class, 'exam_id')->with('student');
    }
}
