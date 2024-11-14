<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{
    use HasFactory;
    protected $guarded = ['id'];


    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }
    // public function class()
    // {
    //     return $this->belongsTo(SchoolClass::class, 'class_id');
    // }
}
