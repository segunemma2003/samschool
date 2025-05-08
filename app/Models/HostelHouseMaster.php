<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostelHouseMaster extends Model
{
    protected $guarded =  ['id'];

    protected $table = 'hostel_house_masters';

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
