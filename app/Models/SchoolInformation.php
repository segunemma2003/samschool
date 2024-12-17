<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolInformation extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function academy(){
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function term(){
        return $this->belongsTo(Term::class, 'term_id');
    }
}
