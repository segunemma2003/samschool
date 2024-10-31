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

    public function section()
    {
        return $this->belongsTo(SchoolSection::class, 'school_section_id');
    }
}
