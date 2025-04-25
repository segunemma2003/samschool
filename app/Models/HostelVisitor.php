<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelVisitor extends Model
{
    protected $guarded = ['id'];

    public function building()
    {
        return $this->belongsTo(HostelBuilding::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
