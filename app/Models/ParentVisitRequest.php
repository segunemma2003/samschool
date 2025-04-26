<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentVisitRequest extends Model
{
    protected $guarded = ['id'];

    public function parent()
    {
        return $this->belongsTo(Guardians::class, 'parent_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function building()
    {
        return $this->belongsTo(HostelBuilding::class, 'hostel_building_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeCurrentYear($query)
    {
        return $query->whereHas('building.currentHouseMaster', function($query) {
            $query->where('academic_year_id', AcademicYear::current()->id);
        });
    }

    public function getCurrentHouseMasterAttribute()
    {
        return $this->building->currentHouseMaster;
    }
}
