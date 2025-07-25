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


    public function scopeWithBasicInfo($query)
    {
        return $query->select([
            'id', 'subject_id', 'assessment_type', 'term_id', 'academic_year_id'
        ])->with([
            'subject:id,name,code',
            'term:id,name',
            'academic:id,title,year'
        ]);
    }


    public function scopeForTeacher($query, $teacherId)
    {
        return $query->whereHas('subject', function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        });
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

    // public function examScore($studentId)
    // {
    //     return $this->hasOne(QuizScore::class, 'exam_id')
    //     ->where('student_id', $studentId);
    // }

    public function examScore($studentId = null)
{
    $query = $this->hasOne(QuizScore::class, 'exam_id');

    if ($studentId) {
        $query->where('student_id', $studentId);
    }

    return $query;
}

    public function allStudentsScore()
    {
        return $this->hasMany(QuizScore::class, 'exam_id');
    }

    public function studentsWithScores()
    {
        return $this->hasMany(QuizScore::class, 'exam_id')->with('student');
    }

    public function resultType()
    {
        return $this->belongsTo(ResultSectionType::class, 'result_section_type_id');
    }
}
