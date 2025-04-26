<?php

namespace App\Observers;

use App\Models\AcademicYear;
use App\Models\HostelHouseMaster;
use HouseMasterService;

class AcademicYearObserver
{
    public function created(AcademicYear $academicYear)
    {
        // When a new academic year is created, end all current assignments
        app(HouseMasterService::class)->endCurrentAssignmentsForAcademicYear(
            AcademicYear::current()->first()
        );
    }

    public function updating(AcademicYear $academicYear)
    {
        // When an academic year is marked as current, end all other current years
        if ($academicYear->is_current) {
            AcademicYear::where('id', '!=', $academicYear->id)
                ->update(['is_current' => false]);

            // End all current house master assignments
            HostelHouseMaster::where('is_current', true)
                ->update([
                    'end_date' => now(),
                    'is_current' => false,
                ]);
        }
    }
}
