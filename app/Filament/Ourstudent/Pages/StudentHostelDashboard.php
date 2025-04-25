<?php

namespace App\Filament\Ourstudent\Pages;

use App\Models\HostelApplication;
use App\Models\HostelAttendance;
use App\Models\HostelInventory;
use App\Models\HostelLeaveApplication;
use App\Models\HostelMaintenanceRequest;
use App\Models\HostelMeal;
use App\Models\HostelNotification;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class StudentHostelDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.ourstudent.pages.student-hostel-dashboard';

    protected static ?string $navigationGroup = 'Hostel';
    protected static ?int $navigationSort = 1;

    public $currentTerm;
    public $application;
    public $assignment;
    public $leaveApplications;
    public $attendances;
    public $notifications;
    public $inventoryItems;
    public $mealSchedule;
    public $startDate;
    public $endDate;
    public $reason;

    public function mount()
    {
        $this->currentTerm = Term::current()->first();
        $user = User::whereId(Auth::id())->first();
        $student = Student::whereEmail($user->email)->first();
        $studentId = $student->id;

        $this->application = HostelApplication::where('student_id', $studentId)
            ->where('term_id', $this->currentTerm->id)
            ->first();

        $this->assignment = $this->application?->assignment;

        $this->leaveApplications = $this->application ? HostelLeaveApplication::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get() : collect();

        $this->attendances = $this->assignment ? HostelAttendance::where('student_id', $studentId)
            ->where('date', '>=', now()->subDays(7))
            ->orderBy('date', 'desc')
            ->get() : collect();
            $user = User::whereId(Auth::id())->first();
            $student = Student::whereEmail($user->email)->first();

        $this->notifications = HostelNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $this->inventoryItems = $this->assignment ? HostelInventory::where('hostel_room_id', $this->assignment->hostel_room_id)
            ->get() : collect();

        $this->mealSchedule = HostelMeal::where('meal_date', '>=', now())
            ->where('meal_date', '<=', now()->addDays(7))
            ->orderBy('meal_date')
            ->orderBy('meal_type')
            ->get();
    }

    public function applyForHostel()
    {
        if ($this->application) {
            Notification::make()
                ->title('You already have an application for this term')
                ->warning()
                ->send();
            return;
        }
        $user = User::whereId(Auth::id())->first();
        $student = Student::whereEmail($user->email)->first();
        $this->application = HostelApplication::create([
            'student_id' => $student->id,
            'term_id' => $this->currentTerm->id,
            'status' => 'pending',
        ]);

        Notification::make()
            ->title('Application submitted successfully')
            ->success()
            ->send();

        $this->mount();
    }

    public function requestLeave()
    {
        $this->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);
        $user = User::whereId(Auth::id())->first();
        $student = Student::whereEmail($user->email)->first();
        HostelLeaveApplication::create([
            'student_id' => $student->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'reason' => $this->reason,
            'status' => 'pending',
        ]);

        $this->reset(['start_date', 'end_date', 'reason']);
        $this->mount();

        Notification::make()
            ->title('Leave request submitted successfully')
            ->success()
            ->send();
    }

    public function markNotificationAsRead($notificationId)
    {
        HostelNotification::where('id', $notificationId)
            ->update(['is_read' => true, 'read_at' => now()]);

        $this->mount();
    }

    public function reportMaintenanceIssue()
    {
        $this->validate([
            'issue_type' => 'required|string',
            'description' => 'required|string|max:1000',
            'priority' => 'required|in:low,medium,high,critical',
        ]);

        HostelMaintenanceRequest::create([
            'hostel_room_id' => $this->assignment->hostel_room_id,
            'reported_by' => Auth::id(),
            'issue_type' => $this->issue_type,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => 'pending',
        ]);

        $this->reset(['issue_type', 'description', 'priority']);

        Notification::make()
            ->title('Maintenance issue reported successfully')
            ->success()
            ->send();

        $this->mount();
    }
}
