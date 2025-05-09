<?php
namespace App\Services;
use App\Models\AcademicYear;
use App\Models\HostelBuilding;
use App\Models\HostelHouseMaster;
use App\Models\HostelNotification;
use App\Models\Teacher;
use App\Models\User;

class HouseMasterService
{
    public function assignHouseMaster(HostelBuilding $building, Teacher $teacher, AcademicYear $academicYear)
    {
        // End any current assignment for this building in this academic year
        HostelHouseMaster::where('hostel_building_id', $building->id)
            ->where('academic_year_id', $academicYear->id)
            ->update([
                'end_date' => now(),
                'is_current' => false,
            ]);

        // End any current assignment for this teacher
        HostelHouseMaster::where('teacher_id', $teacher->id)
            ->where('is_current', true)
            ->update([
                'end_date' => now(),
                'is_current' => false,
            ]);

        // Create new assignment
        $assignment = HostelHouseMaster::create([
            'hostel_building_id' => $building->id,
            'teacher_id' => $teacher->id,
            'academic_year_id' => $academicYear->id,
            'start_date' => now(),
            'is_current' => true,
        ]);

        // Send notifications
        $this->sendAssignmentNotifications($assignment);

        return $assignment;
    }

    public function endCurrentAssignmentsForAcademicYear(AcademicYear $academicYear)
    {
        $assignments = HostelHouseMaster::where('academic_year_id', $academicYear->id)
            ->where('is_current', true)
            ->get();

        foreach ($assignments as $assignment) {
            $assignment->update([
                'end_date' => $academicYear->end_date,
                'is_current' => false,
            ]);
        }

        return $assignments->count();
    }

    protected function sendAssignmentNotifications(HostelHouseMaster $assignment)
    {
        // Notify teacher
        $message = "You have been assigned as House Master for {$assignment->building->name} for {$assignment->academicYear->name}";

        HostelNotification::create([
            'user_id' => $assignment->teacher->user_id,
            'notification_type' => 'house_master_assigned',
            'message' => $message,
            'data' => [
                'building_id' => $assignment->hostel_building_id,
                'academic_year_id' => $assignment->academic_year_id,
            ],
        ]);

        // Notify admin staff
        $adminUsers = User::where('user_type', 'admin')->get();

        foreach ($adminUsers as $user) {
            HostelNotification::create([
                'user_id' => $user->id,
                'notification_type' => 'new_house_master_assignment',
                'message' => "{$assignment->teacher->name} assigned as House Master for {$assignment->building->name}",
                'data' => [
                    'teacher_id' => $assignment->teacher_id,
                    'building_id' => $assignment->hostel_building_id,
                ],
            ]);
        }
    }
}
