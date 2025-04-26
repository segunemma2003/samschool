<?php

namespace App\Filament\Ourparent\Pages;

use App\Models\Guardians;
use App\Models\HostelBuilding;
use App\Models\ParentVisitRequest;
use App\Models\Student;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class RequestHostelVisit extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Hostel';

    protected static ?string $title = 'Request Hostel Visit';


    protected static string $view = 'filament.ourparent.pages.request-hostel-visit';

    public $student_id;
    public $proposed_visit_date;
    public $purpose;
    public $building_id;

    public function mount()
    {
        $this->proposed_visit_date = now()->addDay()->setHour(14)->setMinute(0);
    }

    public function submitRequest()
    {
        $this->validate([
            'student_id' => 'required|exists:students,id',
            'proposed_visit_date' => 'required|date|after:now',
            'purpose' => 'required|string|max:1000',
            'building_id' => 'required|exists:hostel_buildings,id',
        ]);
        $user = User::whereId(Auth::id())->first();
        $parent = Guardians::whereEmail($user->email)->first();
        ParentVisitRequest::create([
            'parent_id' => $parent->id,
            'student_id' => $this->student_id,
            'hostel_building_id' => $this->building_id,
            'proposed_visit_date' => $this->proposed_visit_date,
            'purpose' => $this->purpose,
            'status' => 'pending',
        ]);

        $this->reset(['student_id', 'proposed_visit_date', 'purpose', 'building_id']);

        Notification::make()
            ->title('Visit request submitted successfully')
            ->success()
            ->send();
    }

    public function getStudentsProperty()
    {
        $user = User::whereId(Auth::id())->first();
        $parent = Guardians::whereEmail($user->email)->first();
        $students = Student::where('guardian_id', $parent->id)->get();
        return $students;
    }

    public function getBuildingsProperty()
    {
        return HostelBuilding::all();
    }
}
