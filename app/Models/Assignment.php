<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id'); // Assuming your class model is SchoolClass
    }

    public function section()
    {
        return $this->belongsTo(SchoolSection::class, 'section_id'); // Assuming your class model is SchoolClass
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id'); // Assuming your class model is SchoolClass
    }
}
