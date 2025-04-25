<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelApplication extends Model
{
    protected $guarded = ['id'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignment()
    {
        return $this->hasOne(HostelAssignment::class);
    }

}
