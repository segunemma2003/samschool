<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HouseHostelMaster extends Model
{
    protected $guarded =  ['id'];



    public function building()
    {
        return $this->belongsTo(HostelBuilding::class, 'hostel_building_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
