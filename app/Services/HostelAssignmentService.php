<?php

use App\Models\AcademicYear;
use App\Models\HostelApplication;
use App\Models\HostelRoom;
use App\Models\Term;
use Illuminate\Support\Facades\Auth;

class HostelAssignmentService
{
    public function autoAssignStudents(Term $term, AcademicYear $academic, array $classDistribution)
    {
        $pendingApplications = HostelApplication::where('term_id', $term->id)
            ->where('academic_year_id', $academic->id)
            ->where('status', 'pending')
            ->get();

        $availableRooms = HostelRoom::whereColumn('current_occupancy', '<', 'capacity')
            ->orderBy('current_occupancy')
            ->get();

        $classGroups = $pendingApplications->groupBy(fn ($app) => $app->student->class_id);

        foreach ($classGroups as $classId => $applications) {
            $assignedCount = 0;
            $targetCount = $classDistribution[$classId] ?? 0;

            foreach ($applications as $application) {
                if ($assignedCount >= $targetCount) break;
                if (!$availableRooms->count()) break;

                $room = $availableRooms->first();

                $application->assignment()->create([
                    'hostel_room_id' => $room->id,
                    'student_id' => $application->student_id,
                    'term_id' => $term->id,
                    'assignment_date' => now(),
                ]);

                $room->increment('current_occupancy');
                $application->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);

                $assignedCount++;

                if ($room->current_occupancy >= $room->capacity) {
                    $availableRooms = $availableRooms->filter(fn ($r) => $r->id !== $room->id);
                }
            }
        }
    }
}
