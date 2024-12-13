<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultSectionStudentType extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function resultSection()
    {
        return $this->belongsTo(ResultSection::class, 'result_section_id');
    }
}
