<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelMealAttendance extends Model
{
    protected $guarded = ['id'];

    public function meal()
    {
        return $this->belongsTo(HostelMeal::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
