<?php

namespace App\Filament\Hostel\Resources\HostelMealResource\Pages;

use App\Models\HostelMeal;
use App\Models\HostelMealAttendance;
use App\Models\Student;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use App\Filament\Hostel\Resources\HostelMealResource;

class TakeMealAttendance extends Page
{
    protected static string $resource = HostelMealResource::class;

    protected static ?string $title = 'Take Meal Attendance';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string $view = 'filament.hostel.pages.take-meal-attendance';



    public HostelMeal $record;
    public array $attendance = [];
    public array $specialRequirements = [];

    public function mount(HostelMeal $record)
    {
        $this->record = $record;

        // Initialize attendance data
        $existingAttendances = $record->attendances()->with('student')->get();

        foreach ($existingAttendances as $attendance) {
            $this->attendance[$attendance->student_id] = $attendance->attended;
            $this->specialRequirements[$attendance->student_id] = $attendance->special_requirements;
        }
    }

    public static function getSlug(): string
    {
        return '{record}/take-attendance';
    }

    public function saveAttendance()
    {
        foreach ($this->attendance as $studentId => $attended) {
            HostelMealAttendance::updateOrCreate(
                [
                    'hostel_meal_id' => $this->record->id,
                    'student_id' => $studentId,
                ],
                [
                    'attended' => $attended,
                    'special_requirements' => $this->specialRequirements[$studentId] ?? null,
                ]
            );
        }

        Notification::make()
            ->title('Attendance saved successfully')
            ->success()
            ->send();

        $this->redirect(route('filament.resources.hostel-meals.index'));
    }

    public function getStudentsProperty()
    {
        return Student::whereHas('hostelAssignments', function($query) {
            $query->whereNull('release_date');
        })
        ->with(['hostelAssignments.room.floor.building'])
        ->get()
        ->sortBy('hostelAssignments.room.floor.building.name')
        ->sortBy('hostelAssignments.room.room_number');
    }
}
