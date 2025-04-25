<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelRoom extends Model
{
    protected $guarded = ['id'];

    public function floor()
    {
        return $this->belongsTo(HostelFloor::class);
    }

    public function assignments()
    {
        return $this->hasMany(HostelAssignment::class);
    }

    public function currentAssignments()
    {
        return $this->hasMany(HostelAssignment::class)->whereNull('release_date');
    }
}
