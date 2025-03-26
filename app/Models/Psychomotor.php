<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Psychomotor extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    public function group()
    {
        return $this->belongsTo(StudentGroup::class, 'group_id');
    }

    public function academy()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_id');
    }
    public function term()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function psychomotorStudent()
    {
        return $this->hasMany(PyschomotorStudent::class);
    }
}
