<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelRuleViolation extends Model
{
    protected $guarded = ['id'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function warnings()
    {
        return $this->hasMany(HostelWarning::class);
    }
}
