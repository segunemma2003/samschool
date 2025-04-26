<?php

use App\Mail\HostelNotificationMail;
use App\Models\HostelApplication;
use App\Models\HostelLeaveApplication;
use App\Models\HostelNotification;
use App\Models\ParentVisitRequest;
use App\Models\Student;
use App\Models\User;

class HostelNotificationService
{
    public function sendApplicationApprovalNotification(HostelApplication $application)
    {
        $message = "Your hostel application for {$application->term->name} has been approved. Room: {$application->assignment->room->room_number}";

        HostelNotification::create([
            'user_id' => $application->student->user_id,
            'notification_type' => 'application_approved',
            'message' => $message,
            'data' => [
                'application_id' => $application->id,
                'room_id' => $application->assignment->hostel_room_id,
            ],
        ]);

        // Optionally send email/SMS
        $this->sendEmailNotification($application->student->user, $message);
    }

    public function sendLeaveApprovalNotification(HostelLeaveApplication $leave)
    {
        $message = "Your leave request from {$leave->start_date} to {$leave->end_date} has been {$leave->status}";

        HostelNotification::create([
            'user_id' => $leave->student->user_id,
            'notification_type' => 'leave_processed',
            'message' => $message,
            'data' => [
                'leave_id' => $leave->id,
                'status' => $leave->status,
            ],
        ]);

        $this->sendEmailNotification($leave->student->user, $message);
    }

    public function sendAttendanceAnomalyNotification(Student $student, string $anomalyType)
    {
        $messages = [
            'morning_missing' => "You missed morning attendance on " . now()->format('Y-m-d'),
            'evening_missing' => "You missed evening attendance on " . now()->format('Y-m-d'),
            'late' => "You were late for attendance on " . now()->format('Y-m-d'),
        ];

        HostelNotification::create([
            'user_id' => $student->user_id,
            'notification_type' => 'attendance_anomaly',
            'message' => $messages[$anomalyType],
            'data' => [
                'student_id' => $student->id,
                'anomaly_type' => $anomalyType,
                'date' => now()->format('Y-m-d'),
            ],
        ]);
    }

    public function sendVisitApprovalNotification(ParentVisitRequest $visit)
    {
        $message = "Your visit request to see {$visit->student->name} on {$visit->proposed_visit_date->format('M d, Y h:i A')} has been approved";

        // Notify parent
        HostelNotification::create([
            'user_id' => $visit->parent->user_id,
            'notification_type' => 'visit_approved',
            'message' => $message,
            'data' => [
                'visit_id' => $visit->id,
                'student_id' => $visit->student_id,
            ],
        ]);

        // Notify house master
        if ($visit->building->houseMaster) {
            HostelNotification::create([
                'user_id' => $visit->building->houseMaster->user_id,
                'notification_type' => 'new_approved_visit',
                'message' => "Approved visit for {$visit->parent->name} to see {$visit->student->name} on {$visit->proposed_visit_date->format('M d, Y h:i A')}",
                'data' => [
                    'visit_id' => $visit->id,
                    'parent_id' => $visit->parent_id,
                ],
            ]);
        }
    }

    public function sendNewVisitRequestNotification(ParentVisitRequest $visit)
    {
        if ($visit->building->houseMaster) {
            $message = "New visit request from {$visit->parent->name} to see {$visit->student->name} on {$visit->proposed_visit_date->format('M d, Y h:i A')}";

            HostelNotification::create([
                'user_id' => $visit->building->houseMaster->user_id,
                'notification_type' => 'new_visit_request',
                'message' => $message,
                'data' => [
                    'visit_id' => $visit->id,
                    'parent_id' => $visit->parent_id,
                ],
            ]);
        }
    }
    protected function sendEmailNotification(User $user, string $message)
    {
        // Implement email sending logic
        $user->notify(new HostelNotificationMail($message));
    }
}
