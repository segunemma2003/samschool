<?php

use App\Models\HostelAssignment;
use App\Models\HostelAttendance;
use App\Models\HostelRoom;
use Illuminate\Support\Facades\Auth;

class HostelAttendanceService
{
    public function recordMorningAttendance(array $studentIds)
    {
        $this->recordAttendance($studentIds, 'morning');
    }

    public function recordEveningAttendance(array $studentIds)
    {
        $this->recordAttendance($studentIds, 'evening');
    }

    protected function recordAttendance(array $studentIds, string $session)
    {
        $assignments = HostelAssignment::whereIn('student_id', $studentIds)
            ->whereNull('release_date')
            ->with('room')
            ->get();

        $now = now();
        $currentTime = $now->format('H:i:s');
        $currentDate = $now->toDateString();

        foreach ($assignments as $assignment) {
            $attendance = HostelAttendance::firstOrCreate([
                'student_id' => $assignment->student_id,
                'hostel_room_id' => $assignment->hostel_room_id,
                'date' => $currentDate,
            ]);

            if ($session === 'morning') {
                $attendance->update([
                    'clock_in' => $currentTime,
                    'status' => $currentTime > '08:00:00' ? 'late' : 'present',
                    'recorded_by' => Auth::id(),
                ]);
            } else {
                $attendance->update([
                    'clock_out' => $currentTime,
                    'recorded_by' => Auth::id(),
                ]);
            }
        }
    }

    public function getAttendanceReport(HostelRoom $room, string $startDate, string $endDate)
    {
        return HostelAttendance::where('hostel_room_id', $room->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('student')
            ->get()
            ->groupBy('student_id');
    }
}
