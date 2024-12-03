<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizSubmission extends Model
{
    use HasFactory;
    protected $guarded =['id'];

    public function question()
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }
}
