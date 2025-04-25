<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelAssignment extends Model
{
    protected $guarded = ['id'];

    public function application()
    {
        return $this->belongsTo(HostelApplication::class);
    }

    public function room()
    {
        return $this->belongsTo(HostelRoom::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function attendances()
    {
        return $this->hasMany(HostelAttendance::class, 'student_id', 'student_id');
    }
}
