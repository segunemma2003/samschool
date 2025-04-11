<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArmsTeacher extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function arm()
    {
       return $this->belongsTo(Arm::class, 'arm_id');
    }
    public function teacher()
    {
       return $this->belongsTo(Teacher::class, 'teacher_id');
    }
    public function class()
    {
       return $this->belongsTo(SchoolClass::class, 'class_id');
    }
    public function term()
    {
       return $this->belongsTo(Term::class, 'term_id');
    }
    public function academy()
    {
       return $this->belongsTo(AcademicYear::class, 'academic_id');
    }
}
