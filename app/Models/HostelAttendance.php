<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelAttendance extends Model
{
    protected $guarded = ['id'];
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function room()
    {
        return $this->belongsTo(HostelRoom::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
