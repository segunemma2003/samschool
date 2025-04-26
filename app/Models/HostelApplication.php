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
        return $this->belongsTo(AcademicYear::class, 'academic_id');
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

    public function room()
    {
        return $this->hasMany(HostelRoom::class, 'id', 'room_id');
    }

    public function room_number()
    {
        return $this->hasMany(HostelRoom::class, 'id', 'room_id')->select('id', 'room_number');
    }
}
