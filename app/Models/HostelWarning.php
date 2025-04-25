<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelWarning extends Model
{
    protected $guarded = ['id'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function violation()
    {
        return $this->belongsTo(HostelRuleViolation::class);
    }
}
