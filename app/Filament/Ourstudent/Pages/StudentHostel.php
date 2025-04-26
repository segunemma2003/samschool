<?php

namespace App\Filament\Ourstudent\Pages;

use App\Models\HostelApplication;
use App\Models\HostelAttendance;
use App\Models\HostelLeaveApplication;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class StudentHostel extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.ourstudent.pages.student-hostel';

    protected static ?string $navigationGroup = 'Hostel';

    protected static ?int $navigationSort = 1;


    public $currentTerm;
    public $application;
    public $assignment;
    public $leaveApplications;
    public $attendances;

    public function mount()
    {
        $user = User::where('id', Auth::id())->first();
        $student = Student::whereEmail($user->email)->first();
        $this->currentTerm = Term::whereStatus("true")?->first();
        $this->application = HostelApplication::where('student_id', $student->id)
            ->where('term_id', $this->currentTerm->id)
            ->first();

        $this->assignment = $this->application?->assignment;
        $this->leaveApplications = $this->application ? HostelLeaveApplication::where('student_id', $student->id)
            ->whereIn('status', ['pending', 'approved'])
            ->get() : collect();

        $this->attendances = $this->assignment ? HostelAttendance::where('student_id', $student->id)
            ->where('date', '>=', now()->subDays(7))
            ->orderBy('date', 'desc')
            ->get() : collect();
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
        $user = User::where('id', Auth::id())->first();
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
    }

    public function requestLeave()
    {
        $user = User::where('id', Auth::id())->first();
        $student = Student::whereEmail($user->email)->first();
        $this->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

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

}
