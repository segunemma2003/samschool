<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelBuilding extends Model
{
    protected $guarded = ['id'];
    public function floors()
    {
        return $this->hasMany(HostelFloor::class);
    }

    public function currentHouseMaster()
    {
        return $this->hasOne(HostelHouseMaster::class)
            ->where('is_current', true)
            ->with('teacher');
    }

    public function houseMasters()
    {
        return $this->hasMany(HostelHouseMaster::class)
            ->with(['teacher', 'academicYear'])
            ->orderBy('academic_year_id', 'desc');
    }
}
