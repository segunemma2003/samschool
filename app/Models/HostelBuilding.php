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
        return $this->hasOne(HouseHostelMaster::class)
            ->where('is_current', true)
            ->with('teacher');
    }

    public function houseMasters()
    {
        return $this->hasMany(HouseHostelMaster::class)
            ->with(['teacher', 'academicYear'])
            ->orderBy('academic_year_id', 'desc');
    }

    public function rooms()
    {
        return $this->hasManyThrough(HostelRoom::class, HostelFloor::class, 'hostel_building_id', 'hostel_floor_id');
    }

    public function scopeWithCurrentMaster($query)
    {
        return $query->with([
            'currentHouseMaster.teacher:id,name,email',
            'currentHouseMaster.academicYear:id,title'
        ]);
    }
}
